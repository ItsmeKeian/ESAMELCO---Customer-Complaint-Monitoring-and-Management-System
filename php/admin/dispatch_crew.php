<?php
session_start();
require_once '../../php/dbconnect.php';

header('Content-Type: application/json');

// Auth guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$action = $_GET['action'] ?? 'load';

try {

    // ── ACTION: Load pending complaints + available crew ─────────
    if ($action === 'load') {

        // Pending complaints (not yet assigned)
        $complaints = $pdo->query("
            SELECT
                c.id,
                c.ticket_no,
                c.complaint_type,
                c.description,
                c.latitude,
                c.longitude,
                c.created_at,
                u.full_name AS consumer_name,
                u.phone     AS consumer_phone
            FROM complaints c
            JOIN users u ON c.consumer_id = u.id
            WHERE c.status = 'pending'
              AND c.id NOT IN (
                  SELECT complaint_id FROM assignments WHERE status = 'active'
              )
            ORDER BY c.created_at ASC
        ")->fetchAll();

        // Available crew with their last known location
        $crew = $pdo->query("
            SELECT
                u.id,
                u.full_name,
                u.phone,
                cl.latitude,
                cl.longitude,
                cl.updated_at AS last_seen
            FROM users u
            JOIN crew_locations cl ON u.id = cl.crew_id
            WHERE u.role   = 'crew'
              AND u.status = 'active'
              AND u.id NOT IN (
                  SELECT crew_id FROM assignments WHERE status = 'active'
              )
        ")->fetchAll();

        echo json_encode([
            'success'    => true,
            'complaints' => $complaints,
            'crew'       => $crew,
        ]);
    }

    // ── ACTION: Get crew list for a specific complaint (with ETA) ─
    elseif ($action === 'get_crew') {

        $complaint_id = intval($_GET['complaint_id'] ?? 0);

        // Get complaint location
        $cStmt = $pdo->prepare("SELECT latitude, longitude FROM complaints WHERE id = ?");
        $cStmt->execute([$complaint_id]);
        $complaint = $cStmt->fetch();

        // Get all available crew with locations
        $crew = $pdo->query("
            SELECT
                u.id,
                u.full_name,
                u.phone,
                cl.latitude,
                cl.longitude,
                cl.updated_at AS last_seen
            FROM users u
            JOIN crew_locations cl ON u.id = cl.crew_id
            WHERE u.role   = 'crew'
              AND u.status = 'active'
              AND u.id NOT IN (
                  SELECT crew_id FROM assignments WHERE status = 'active'
              )
        ")->fetchAll();

        // Calculate ETA using Haversine formula (average speed: 40 km/h)
        $SPEED_KMH = 40;

        foreach ($crew as &$c) {
            if ($complaint && $complaint['latitude'] && $c['latitude']) {
                $dist_km    = haversine(
                    (float)$complaint['latitude'],
                    (float)$complaint['longitude'],
                    (float)$c['latitude'],
                    (float)$c['longitude']
                );
                $eta_minutes        = round(($dist_km / $SPEED_KMH) * 60);
                $c['distance_km']   = round($dist_km, 2);
                $c['eta_minutes']   = $eta_minutes;
            } else {
                $c['distance_km'] = null;
                $c['eta_minutes'] = null;
            }
        }

        // Sort by ETA ascending (nearest first)
        usort($crew, fn($a, $b) => ($a['eta_minutes'] ?? 9999) <=> ($b['eta_minutes'] ?? 9999));

        echo json_encode(['success' => true, 'crew' => $crew]);
    }

    // ── ACTION: Dispatch a crew to a complaint ───────────────────
    elseif ($action === 'dispatch' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $complaint_id = intval($_POST['complaint_id'] ?? 0);
        $crew_id      = intval($_POST['crew_id']      ?? 0);
        $eta_minutes  = intval($_POST['eta_minutes']  ?? 0);

        if (!$complaint_id || !$crew_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid complaint or crew.']);
            exit();
        }

        // Check not already assigned
        $check = $pdo->prepare("SELECT id FROM assignments WHERE complaint_id = ? AND status = 'active'");
        $check->execute([$complaint_id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'This complaint already has an active assignment.']);
            exit();
        }

        // Insert assignment
        $pdo->prepare("
            INSERT INTO assignments (complaint_id, crew_id, status, eta_minutes)
            VALUES (?, ?, 'active', ?)
        ")->execute([$complaint_id, $crew_id, $eta_minutes]);

        // Update complaint status to ongoing
        $pdo->prepare("UPDATE complaints SET status = 'ongoing' WHERE id = ?")
            ->execute([$complaint_id]);

        // Get ticket_no and consumer_id for notification
        $info = $pdo->prepare("SELECT ticket_no, consumer_id FROM complaints WHERE id = ?");
        $info->execute([$complaint_id]);
        $row = $info->fetch();

        // Get crew name
        $crewName = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
        $crewName->execute([$crew_id]);
        $crew = $crewName->fetch();

        // Notify consumer
        if ($row) {
            $title   = "Crew Dispatched";
            $message = "A maintenance crew ({$crew['full_name']}) has been dispatched for your complaint ({$row['ticket_no']}). ETA: {$eta_minutes} minutes.";
            $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")
                ->execute([$row['consumer_id'], $title, $message]);
        }

        echo json_encode(['success' => true, 'message' => 'Crew dispatched successfully!']);
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

// ── Haversine Formula ────────────────────────────────────────────
// Returns distance in kilometers between two GPS coordinates
function haversine($lat1, $lon1, $lat2, $lon2) {
    $R    = 6371; // Earth radius in km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a    = sin($dLat / 2) * sin($dLat / 2)
          + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
          * sin($dLon / 2) * sin($dLon / 2);
    $c    = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $R * $c;
}