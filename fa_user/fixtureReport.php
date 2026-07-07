<?php 
require_once 'header.php';  

// DB connection config
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'fa_db';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get card stats for a given match and team ID
function getTeamCardStats($conn, $matchId, $teamId) {
    $sql = "
        SELECT c.card_type, COUNT(*) AS total
        FROM cards c
        JOIN team_members tm ON c.member_id = tm.member_id
        WHERE c.match_id = ? AND tm.team = ?
        GROUP BY c.card_type
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $matchId, $teamId);
    $stmt->execute();
    $result = $stmt->get_result();

    $cards = ['yellow' => 0, 'double_yellow' => 0, 'red' => 0];
    while ($row = $result->fetch_assoc()) {
        $cards[$row['card_type']] = $row['total'];
    }
    $stmt->close();

    return $cards;
}

// Function to get card stats for a given match and team name (legacy support)
function getCardStats($conn, $matchId, $teamName) {
    $sql = "
        SELECT c.card_type, COUNT(*) AS total
        FROM cards c
        JOIN team_members m ON c.member_id = m.member_id
        JOIN team t ON m.team = t.team_id
        WHERE c.match_id = ? AND t.name = ?
        GROUP BY c.card_type
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("is", $matchId, $teamName);
    $stmt->execute();
    $result = $stmt->get_result();

    $cards = ['yellow' => 0, 'double_yellow' => 0, 'red' => 0];
    while ($row = $result->fetch_assoc()) {
        $cards[$row['card_type']] = $row['total'];
    }
    $stmt->close();

    return $cards;
}

// Query all matches with team information ordered by date descending
$sqlMatches = "
    SELECT
        m.*,
        t1.name AS team1_name,
        t1.logon AS team1_logo,
        t2.name AS team2_name,
        t2.logon AS team2_logo
    FROM `match` m
    JOIN `team` t1 ON m.team1_id = t1.team_id
    JOIN `team` t2 ON m.team2_id = t2.team_id
    ORDER BY m.match_date DESC, m.match_time DESC
";
$resultMatches = $conn->query($sqlMatches);
if (!$resultMatches) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Match List with Cards</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />



    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
        }
        .container {
            margin-top: 2rem;
            padding-left: 10rem; /* Adjust as needed for your sidebar/header.php layout */
            padding-right: 2rem;
        }
        h2 {
            color: #0056b3;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }
        .table-responsive {
            margin-top: 1.5rem;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.03);
        }
        .table-dark {
            background-color: #212529;
            color: #fff;
        }
        .table-dark th {
            border-color: #454d55;
        }
        .table thead th {
            border-bottom: 2px solid #dee2e6;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            vertical-align: middle;
        }
        .table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            font-size: 0.875rem;
        }
        tbody tr:hover {
            background-color: #e2e6ea !important;
            cursor: pointer;
        }
        .badge-card {
            margin-right: 6px;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.4em 0.6em;
            border-radius: 0.35rem;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }
        .card-emoji {
            margin-right: 0.3em;
            font-size: 1em;
        }
        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #343a40 !important;
        }
        .badge.bg-danger {
            background-color: #dc3545 !important;
        }
        .badge.bg-secondary {
            background-color: #6c757d !important;
        }
        .badge.bg-success {
            background-color: #28a745 !important;
        }
        .badge.bg-info {
            background-color: #17a2b8 !important;
        }

        /* DataTables + Bootstrap styles */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
            margin-left: 0.5rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
            margin-right: 0.5rem;
        }
        .dataTables_wrapper .dt-buttons {
            margin-bottom: 1rem;
        }
        .dataTables_wrapper .dt-buttons .btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 0.375rem 0.75rem;
            margin-right: 0.5rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .dataTables_wrapper .dt-buttons .btn:hover {
            background-color: #0056b3;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5em 0.8em;
            border: 1px solid #dee2e6;
            margin-left: -1px;
            cursor: pointer;
            background-color: #fff;
            color: #007bff;
            transition: background-color 0.2s, color 0.2s;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
            background-color: #e9ecef;
            color: #0056b3;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #6c757d !important;
            cursor: default;
            background-color: #fff;
        }

        /* Custom download modal styles */
        .download-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(3px);
        }

        .download-modal-content {
            background-color: #ffffff;
            margin: 8% auto;
            padding: 30px;
            border-radius: 15px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .download-option {
            display: flex;
            align-items: center;
            padding: 20px;
            margin: 15px 0;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .download-option:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,123,255,0.2);
        }

        .download-option.selected {
            border-color: #007bff;
            background-color: #e3f2fd;
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
        }

        .download-icon {
            font-size: 2.5rem;
            margin-right: 20px;
            width: 60px;
            text-align: center;
        }

        .download-info {
            flex: 1;
        }

        .download-info h5 {
            margin: 0 0 5px 0;
            color: #333;
            font-weight: 600;
        }

        .download-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .download-check {
            font-size: 1.5rem;
            color: #28a745;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .download-option.selected .download-check {
            opacity: 1;
        }

        .download-option.downloading {
            border-color: #007bff;
            background-color: #e3f2fd;
            animation: downloadPulse 1.5s infinite;
        }

        @keyframes downloadPulse {
            0% { box-shadow: 0 4px 15px rgba(0,123,255,0.3); }
            50% { box-shadow: 0 6px 20px rgba(0,123,255,0.5); }
            100% { box-shadow: 0 4px 15px rgba(0,123,255,0.3); }
        }

        .download-action {
            margin-top: 8px;
        }

        .single-option {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
            border-color: #dc3545;
        }

        .single-option:hover {
            border-color: #c82333;
            background: linear-gradient(135deg, #fff0f0 0%, #ffe0e0 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(220, 53, 69, 0.3);
        }

        #customDownloadBtn {
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        #customDownloadBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            background-color: #218838;
        }

        #customDownloadBtn:active {
            transform: translateY(0);
        }

        .download-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            border: 1px solid #dee2e6;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .dataTables_wrapper .dt-buttons .btn {
                display: block;
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
        @media (max-width: 576px) {
            table {
                font-size: 0.8rem;
            }
            .table thead th, .table tbody td {
                padding: 0.5rem 0.75rem;
            }
            .badge-card {
                font-size: 0.7rem;
                padding: 0.3em 0.5em;
            }
            .download-modal-content {
                width: 95%;
                margin: 5% auto;
                padding: 20px;
            }
            .download-option {
                padding: 15px;
            }
            .download-icon {
                font-size: 2rem;
                width: 50px;
                margin-right: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4 text-primary">Match List with Cards</h2>

    <div class="table-responsive shadow-sm rounded">
        <table id="matchesTable" class="table table-striped table-bordered align-middle w-100">
            <thead class="table-dark">
                <tr>
                    <th>Match</th>
                    <th>Week</th>
                    <th>Stadium</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Cards (Team 1)</th>
                    <th>Cards (Team 2)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultMatches->fetch_assoc()):
                    $matchId = $row['id'];
                    $team1Id = $row['team1_id'];
                    $team2Id = $row['team2_id'];
                    $team1Name = $row['team1_name'];
                    $team2Name = $row['team2_name'];

                    // Get card stats for each team using team IDs
                    $cardsTeam1 = getTeamCardStats($conn, $matchId, $team1Id);
                    $cardsTeam2 = getTeamCardStats($conn, $matchId, $team2Id);
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($team1Name) ?></strong> vs <strong><?= htmlspecialchars($team2Name) ?></strong></td>
                    <td><?= htmlspecialchars($row['week']) ?></td>
                    <td><?= htmlspecialchars($row['stadium']) ?></td>
                    <td><?= htmlspecialchars($row['match_date']) ?></td>
                    <td><?= htmlspecialchars($row['match_time']) ?></td>
                    <td>
                        <span class="text-capitalize badge <?=
                            $row['status'] === 'live' ? 'bg-success' :
                            ($row['status'] === 'completed' ? 'bg-secondary' : 'bg-info') ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'live' || $row['status'] === 'completed'): ?>
                            <strong><?= $row['team1_goal'] ?? 0 ?> - <?= $row['team2_goal'] ?? 0 ?></strong>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <?php
                            if ($cardsTeam1['yellow'] > 0) {
                                echo "<span class='badge badge-card bg-warning text-dark'><span class='card-emoji'>🟨</span> {$cardsTeam1['yellow']}</span>";
                            }
                            if ($cardsTeam1['double_yellow'] > 0) {
                                echo "<span class='badge badge-card bg-secondary text-white'><span class='card-emoji'>🟨🟨</span> {$cardsTeam1['double_yellow']}</span>";
                            }
                            if ($cardsTeam1['red'] > 0) {
                                echo "<span class='badge badge-card bg-danger'><span class='card-emoji'>🔴</span> {$cardsTeam1['red']}</span>";
                            }
                            if ($cardsTeam1['yellow'] == 0 && $cardsTeam1['double_yellow'] == 0 && $cardsTeam1['red'] == 0) {
                                echo "<span class='text-muted'>No cards</span>";
                            }
                            ?>
                        </div>
                        <small class="text-muted"><?= htmlspecialchars($team1Name) ?></small>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <?php
                            if ($cardsTeam2['yellow'] > 0) {
                                echo "<span class='badge badge-card bg-warning text-dark'><span class='card-emoji'>🟨</span> {$cardsTeam2['yellow']}</span>";
                            }
                            if ($cardsTeam2['double_yellow'] > 0) {
                                echo "<span class='badge badge-card bg-secondary text-white'><span class='card-emoji'>🟨🟨</span> {$cardsTeam2['double_yellow']}</span>";
                            }
                            if ($cardsTeam2['red'] > 0) {
                                echo "<span class='badge badge-card bg-danger'><span class='card-emoji'>🔴</span> {$cardsTeam2['red']}</span>";
                            }
                            if ($cardsTeam2['yellow'] == 0 && $cardsTeam2['double_yellow'] == 0 && $cardsTeam2['red'] == 0) {
                                echo "<span class='text-muted'>No cards</span>";
                            }
                            ?>
                        </div>
                        <small class="text-muted"><?= htmlspecialchars($team2Name) ?></small>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Download Button Section -->
    <div class="download-section text-center">
        <div class="mb-3">
            <h5 class="text-dark mb-2">
                <i class="fas fa-file-pdf me-2 text-danger"></i>Export PDF Report
            </h5>
            <p class="text-muted mb-0">Download the match table data as a professional PDF report</p>
        </div>

        <button id="customDownloadBtn" class="btn btn-danger btn-lg px-5 py-3">
            <i class="fas fa-file-pdf me-2"></i> Download PDF Report
        </button>

        <!-- Direct download links for testing -->
        <div class="mt-3 d-flex gap-2 justify-content-center">
            <a href="download_table_pdf.php?download=yes" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-download me-1"></i> Force Download
            </a>
            <a href="download_table_pdf.php?download=no" class="btn btn-outline-info btn-sm" target="_blank">
                <i class="fas fa-eye me-1"></i> View in Browser
            </a>
        </div>

        <div class="mt-3">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Click to download the table data as a PDF document
            </small>
        </div>

        <div class="mt-2">
            <small class="text-muted">
                <i class="fas fa-file-pdf text-danger me-1"></i>Professional PDF format - perfect for printing and sharing
            </small>
        </div>
    </div>
</div>

<!-- Custom Download Modal -->
<div id="downloadModal" class="download-modal">
    <div class="download-modal-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-file-pdf"></i> Download PDF Report</h5>
            <button type="button" class="btn-close" onclick="closeDownloadModal()" aria-label="Close"></button>
        </div>

        <p class="text-muted mb-3">Click the button below to download the table data as PDF:</p>

        <div class="download-option single-option" onclick="downloadTablePDF()">
            <div class="download-icon text-danger">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="download-info">
                <h5>PDF Table Report</h5>
                <p>Clean table data in PDF format - perfect for printing and sharing</p>
                <div class="download-action">
                    <small class="text-primary"><i class="fas fa-mouse-pointer me-1"></i>Click to download PDF</small>
                </div>
            </div>
            <div class="download-check">
                <i class="fas fa-download"></i>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            <button type="button" class="btn btn-secondary" onclick="closeDownloadModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
let dataTable;
let selectedFormat = null;

$(document).ready(function() {
    // Initialize DataTable
    dataTable = $('#matchesTable').DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"B>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'copyHtml5',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-primary btn-sm me-1',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm me-1 d-none', // Hidden - we'll use custom
                filename: 'Match_Report_' + new Date().toISOString().slice(0,10),
                title: 'Match Report with Cards',
                exportOptions: {
                    columns: ':visible',
                    modifier: {
                        page: 'all'
                    }
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-info btn-sm me-1'
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm me-1 d-none', // Hidden - we'll use custom
                filename: 'Match_Report_' + new Date().toISOString().slice(0,10),
                title: 'Match Report with Cards',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: ':visible',
                    modifier: {
                        page: 'all'
                    }
                },
                customize: function(doc) {
                    // Customize PDF styling
                    doc.defaultStyle.fontSize = 8;
                    doc.styles.tableHeader.fontSize = 9;
                    doc.styles.tableHeader.fillColor = '#2c3e50';
                    doc.styles.tableHeader.color = 'white';
                    doc.styles.title.fontSize = 16;
                    doc.styles.title.alignment = 'center';
                    doc.styles.title.color = '#2c3e50';

                    // Add header
                    doc.content.splice(0, 0, {
                        text: 'Smart Football - Match Report',
                        style: 'title',
                        margin: [0, 0, 0, 20]
                    });

                    // Add generation date
                    doc.content.splice(1, 0, {
                        text: 'Generated on: ' + new Date().toLocaleDateString(),
                        style: { fontSize: 10, alignment: 'center' },
                        margin: [0, 0, 0, 20]
                    });
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-secondary btn-sm'
            },
            {
                extend: 'colvis',
                text: '<i class="fas fa-columns"></i> Columns',
                className: 'btn btn-dark btn-sm ms-2'
            },
            {
                text: '<i class="fas fa-sync-alt"></i> Refresh',
                className: 'btn btn-warning btn-sm ms-2',
                action: function ( e, dt, node, config ) {
                    location.reload();
                }
            }
        ],
        order: [[3, 'desc']], // sort by date column descending
        pageLength: 10,
        responsive: true,
        columnDefs: [
            { targets: [6, 7, 8], orderable: false } // Score and Cards columns not sortable
        ]
    });

    // Custom download button event
    $('#customDownloadBtn').on('click', function() {
        console.log('Download button clicked'); // Debug log

        // Direct download approach
        window.location.href = 'download_table_pdf.php?download=yes';

        // Show success message
        setTimeout(() => {
            showSuccessMessage('PDF download started successfully!');
        }, 500);
    });
});

// Download modal functions
function openDownloadModal() {
    console.log('Opening download modal'); // Debug log
    document.getElementById('downloadModal').style.display = 'block';
    selectedFormat = null;
    $('.download-option').removeClass('selected downloading');
}

function closeDownloadModal() {
    document.getElementById('downloadModal').style.display = 'none';
    selectedFormat = null;
    $('.download-option').removeClass('selected downloading');
}

function downloadTablePDF() {
    console.log('downloadTablePDF function called'); // Debug log

    // Show loading state for the PDF option
    const selectedOption = $('.download-option.single-option');
    selectedOption.addClass('downloading');
    selectedOption.find('.download-check').html('<i class="fas fa-spinner fa-spin"></i>');
    selectedOption.find('.download-info h5').append(' <small class="text-primary">(Preparing PDF...)</small>');

    // Disable option during download
    selectedOption.css('pointer-events', 'none').css('opacity', '0.8');

    // Start PDF download using PHP script
    setTimeout(() => {
        console.log('Starting download...'); // Debug log

        // Use window.open to trigger download
        window.open('download_table_pdf.php', '_blank');

        console.log('Download window opened'); // Debug log

        // Show success message
        showSuccessMessage('PDF table download started successfully!');

        // Close modal after download
        setTimeout(() => {
            closeDownloadModal();
        }, 1500);

    }, 800);
}







function showSuccessMessage(message) {
    // Create and show success toast
    const toast = $(`
        <div class="toast align-items-center text-white bg-success border-0" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 10000;">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);

    $('body').append(toast);
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();

    // Remove toast after it's hidden
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('downloadModal');
    if (event.target === modal) {
        closeDownloadModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDownloadModal();
    }
});
</script>

<!-- Bootstrap JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$conn->close();
require_once 'footer.php';
?>
