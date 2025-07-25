<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $dataFile = 'leaves.json';
        
        // Load existing data
        $leaves = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
        
        if ($_POST['action'] === 'add_leave') {
            // Add new leave
            $date = $_POST['date'];
            $name = $_POST['name'];
            $type = $_POST['type'];
            
            if (!isset($leaves[$date])) {
                $leaves[$date] = [];
            }
            
            $leaves[$date][$name] = $type;
            
            // Save back to file
            file_put_contents($dataFile, json_encode($leaves));
        } elseif ($_POST['action'] === 'delete_leave') {
            // Remove leave
            $date = $_POST['date'];
            $name = $_POST['name'];
            
            if (isset($leaves[$date][$name])) {
                unset($leaves[$date][$name]);
                
                // If no more leaves for this date, remove the date entry
                if (empty($leaves[$date])) {
                    unset($leaves[$date]);
                }
                
                // Save back to file
                file_put_contents($dataFile, json_encode($leaves));
            }
        }
        
        // Return JSON response for AJAX calls
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
}

// Load leaves data
$dataFile = 'leaves.json';
$leaves = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Resource Leave Tracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="leaveStyle.css">
</head>
<body>
    <!--div class="container-fluid">
        <div class="row">
            <div class="col-md-9">
                <h1 class="mt-3">Resource Leave Tracker</h1>
                <div id="calendar" class="mt-4"></div>
            </div>
            <div class="col-md-3">
                <div class="sidebar">
                    <h3 class="mt-3">Leave Summary</h3>
                    <div id="month-year" class="text-center mb-3"></div>
                    <div id="leave-summary"></div>
                </div>
            </div>
        </div>
    </div-->

    <div class="container-fluid px-0">
        <div class="row g-0">
            <div class="col-12 col-lg-9">
                <div class="p-3">
                    <h1 class="h4 mt-2">Resource Leave Tracker</h1>
                    <div id="calendar" class="mt-3"></div>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="sidebar p-3">
                    <h3 class="h5 mt-2">Leave Summary</h3>
                    <div id="month-year" class="text-center mb-2 small"></div>
                    <div id="leave-summary" class="small"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Entry Modal -->
    <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leaveModalLabel">Add Leave</h5>
                    <div id="modalDateDisplay" class="ms-3 text-muted"></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="leaveForm">
                        <input type="hidden" id="leaveDate" name="date">
                        <input type="hidden" name="action" value="add_leave">
                        <div class="mb-3">
                            <label for="name" class="form-label">Resource Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Leave Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="planned">Planned Leave</option>
                                <option value="adhoc">Ad-hoc Leave</option>
                                <option value="holiday">Holiday</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveLeave">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Details Modal -->
    <div class="modal fade" id="leaveDetailsModal" tabindex="-1" aria-labelledby="leaveDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leaveDetailsModalLabel">Leave Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="leaveDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize calendar
            const calendarEl = document.getElementById('calendar');
            const leaveModal = new bootstrap.Modal(document.getElementById('leaveModal'));
            const leaveDetailsModal = new bootstrap.Modal(document.getElementById('leaveDetailsModal'));
            
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek'
                },
                weekends: false, // Hide weekends
                businessHours: {
                    daysOfWeek: [1, 2, 3, 4, 5], // Monday - Friday
                },
                dateClick: function(info) {
                    // Open modal to add leave
                    document.getElementById('leaveDate').value = info.dateStr;
                    document.getElementById('name').value = '';
                    document.getElementById('type').value = 'planned';
                    document.getElementById('modalDateDisplay').textContent = formatDateForDisplay(info.dateStr);
                    leaveModal.show();
                },
                eventClick: function(info) {
                    // Show leave details
                    const date = info.event.startStr;
                    $.get('get_leaves.php', {date: date}, function(data) {
                        let content = `<h6>${date}</h6><ul class="list-group">`;
                        
                        for (const [name, type] of Object.entries(data.leaves)) {
                            const badgeClass = type === 'planned' ? 'bg-primary' : 
                                             type === 'adhoc' ? 'bg-warning' : 'bg-success';
                            content += `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${name}
                                    <span class="badge ${badgeClass}">${type}</span>
                                    <button class="btn btn-sm btn-danger delete-leave" 
                                            data-date="${date}" data-name="${name}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </li>
                            `;
                        }
                        
                        content += `</ul>`;
                        document.getElementById('leaveDetailsContent').innerHTML = content;
                        leaveDetailsModal.show();
                        
                        // Attach delete handlers
                        document.querySelectorAll('.delete-leave').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const date = this.getAttribute('data-date');
                                const name = this.getAttribute('data-name');
                                
                                $.post('', {
                                    action: 'delete_leave',
                                    date: date,
                                    name: name,
                                    ajax: true
                                }, function() {
                                    calendar.refetchEvents();
                                    updateLeaveSummary();
                                    leaveDetailsModal.hide();
                                });
                            });
                        });
                    });
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    $.get('get_leaves.php', {
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr
                    }, function(data) {
                        const events = [];
                        
                        for (const [date, leaves] of Object.entries(data.leaves)) {
                            const count = Object.keys(leaves).length;
                            let color = '#3788d8'; // Default blue for planned
                            
                            // Check if any leave is adhoc or holiday
                            const types = Object.values(leaves);
                            if (types.includes('holiday')) {
                                color = '#28a745'; // Green for holiday
                            } else if (types.includes('adhoc')) {
                                color = '#ffc107'; // Yellow for adhoc
                            }
                            
                            events.push({
                                title: `${count} leave${count > 1 ? 's' : ''}`,
                                start: date,
                                allDay: true,
                                color: color,
                                display: 'background',
                                textColor: '#000'
                            });
                        }
                        
                        successCallback(events);
                    });
                },
                datesSet: function(info) {
                    // Update month-year display
                    const monthYear = info.view.title;
                    document.getElementById('month-year').textContent = monthYear;
                    
                    // Update leave summary
                    updateLeaveSummary(info.start, info.end);
                }
            });
            
            calendar.render();
            
            // Replace the existing save leave handler with this:
            document.getElementById('saveLeave').addEventListener('click', function() {
            const form = document.getElementById('leaveForm');
            const formData = {
                date: document.getElementById('leaveDate').value,
                name: document.getElementById('name').value,
                type: document.getElementById('type').value,
                action: 'add_leave',
                ajax: true
            };
            
            $.ajax({
                url: '',
                type: 'POST',
                data: formData,
                success: function() {
                    calendar.refetchEvents();
                    updateLeaveSummary();
                    leaveModal.hide();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Failed to save leave. Please try again.');
                }
            });
        });
            
            // Function to update leave summary
            function updateLeaveSummary(start, end) {
                const view = calendar.view;
                start = start || view.activeStart;
                end = end || view.activeEnd;
                
                $.get('get_leaves.php', {
                    start: start.toISOString(),
                    end: end.toISOString()
                }, function(data) {
                    // Count leaves by person
                    const personLeaves = {};
                    
                    for (const [date, leaves] of Object.entries(data.leaves)) {
                        for (const [name, type] of Object.entries(leaves)) {
                            if (!personLeaves[name]) {
                                personLeaves[name] = {
                                    count: 0,
                                    dates: []
                                };
                            }
                            personLeaves[name].count++;
                            personLeaves[name].dates.push({
                                date: date,
                                type: type
                            });
                        }
                    }
                    
                    // Sort by count descending
                    const sorted = Object.entries(personLeaves).sort((a, b) => b[1].count - a[1].count);
                    
                    // Generate HTML
                    let html = '<div class="list-group">';
                    
                    if (sorted.length === 0) {
                        html += '<div class="list-group-item">No leaves recorded this month</div>';
                    } else {
                        for (const [name, data] of sorted) {
                            html += `
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    ${name}
                                    <span class="badge bg-primary rounded-pill">${data.count}</span>
                                    <button class="btn btn-sm btn-info view-dates" 
                                            data-name="${name}" 
                                            data-dates='${JSON.stringify(data.dates)}'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                    
                    html += '</div>';
                    document.getElementById('leave-summary').innerHTML = html;
                    
                    // Attach click handlers to view dates buttons
                    document.querySelectorAll('.view-dates').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const name = this.getAttribute('data-name');
                            const dates = JSON.parse(this.getAttribute('data-dates'));
                            
                            let content = `<h6>${name}'s Leaves</h6><ul>`;
                            
                            dates.forEach(item => {
                                const badgeClass = item.type === 'planned' ? 'bg-primary' : 
                                                 item.type === 'adhoc' ? 'bg-warning' : 'bg-success';
                                content += `
                                    <li>
                                        ${item.date} 
                                        <span class="badge ${badgeClass}">${item.type}</span>
                                    </li>
                                `;
                            });
                            
                            content += '</ul>';
                            document.getElementById('leaveDetailsContent').innerHTML = content;
                            leaveDetailsModal.show();
                        });
                    });
                });
            }

            function formatDateForDisplay(dateStr) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateStr).toLocaleDateString(undefined, options);
        }
        });
    </script>
</body>
</html>