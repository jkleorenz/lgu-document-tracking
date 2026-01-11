@props(['document'])

<style>
    /* Department Journey Progress */
    .department-journey {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        margin-bottom: 25px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .department-journey-title {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .journey-steps {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        margin-bottom: 10px;
    }
    
    .journey-line {
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 3px;
        background: #e9ecef;
        z-index: 0;
    }
    
    .journey-line-active {
        position: absolute;
        top: 20px;
        left: 0;
        height: 3px;
        background: #0d6efd;
        z-index: 1;
        transition: width 0.5s ease;
    }
    
    .journey-step {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }
    
    .journey-step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        border: 3px solid #e9ecef;
        margin: 0 auto 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #6c757d;
        transition: all 0.3s ease;
    }
    
    .journey-step.active .journey-step-circle {
        background: #0d6efd;
        border-color: #0d6efd;
        color: white;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }
    
    .journey-step.completed .journey-step-circle {
        background: #198754;
        border-color: #198754;
        color: white;
    }
    
    .journey-step-label {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 500;
        max-width: 100px;
        margin: 0 auto;
    }
    
    .journey-step.active .journey-step-label {
        color: #0d6efd;
        font-weight: 600;
    }
    
    .journey-step.completed .journey-step-label {
        color: #198754;
    }
    
    /* Simple Timeline */
    .document-timeline {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .timeline-item {
        position: relative;
        padding-left: 50px;
        padding-bottom: 30px;
        border-left: 2px solid #dee2e6;
        margin-left: 20px;
    }
    
    .timeline-item:last-child {
        border-left-color: transparent;
        padding-bottom: 0;
    }
    
    .timeline-icon {
        position: absolute;
        left: -21px;
        top: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    
    .timeline-icon.status-created { border-color: #0d6efd; color: #0d6efd; }
    .timeline-icon.status-pending { border-color: #ffc107; color: #ffc107; }
    .timeline-icon.status-received { border-color: #198754; color: #198754; }
    .timeline-icon.status-review { border-color: #6610f2; color: #6610f2; }
    .timeline-icon.status-forwarded { border-color: #0dcaf0; color: #0dcaf0; }
    .timeline-icon.status-approved { border-color: #198754; color: #198754; }
    .timeline-icon.status-rejected { border-color: #dc3545; color: #dc3545; }
    .timeline-icon.status-archived { border-color: #6c757d; color: #6c757d; }
    .timeline-icon.status-current { border-color: #0d6efd; background: #0d6efd; color: white; }
    .timeline-icon.status-pending-verification { border-color: #ffc107; color: #ffc107; }
    .timeline-icon.status-under-review { border-color: #6610f2; color: #6610f2; }
    
    .timeline-content {
        padding-bottom: 15px;
    }
    
    .timeline-content .badge {
        font-weight: 600;
        padding: 4px 10px;
    }
    
    .timeline-title {
        font-weight: 600;
        font-size: 1rem;
        color: #212529;
        margin-bottom: 8px;
    }
    
    .timeline-date {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 8px;
    }
    
    .timeline-meta {
        color: #495057;
        font-size: 0.9rem;
        margin-top: 4px;
    }
    
    .timeline-user {
        font-size: 0.875rem;
        color: #495057;
    }
    
    .timeline-remarks {
        background: #fff3cd;
        border-left: 3px solid #ffc107;
        padding: 10px 12px;
        border-radius: 4px;
        font-size: 0.875rem;
        color: #856404;
        margin-top: 10px;
    }
    
    @media (max-width: 768px) {
        .journey-steps {
            flex-direction: column;
            align-items: stretch;
        }
        
        .journey-line,
        .journey-line-active {
            top: 0;
            left: 20px;
            width: 3px;
            height: 100%;
        }
        
        .journey-step {
            display: flex;
            align-items: center;
            text-align: left;
            margin-bottom: 20px;
        }
        
        .journey-step-circle {
            margin: 0 15px 0 0;
        }
        
        .journey-step-label {
            margin: 0;
            max-width: none;
        }
    }
</style>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clock-history"></i> Document History
        </h5>
    </div>
    <div class="card-body">
        @php
            $statusLogs = $document->statusLogs()->orderBy('id', 'desc')->get();
            
            // Track department journey
            $departments = collect();
            
            // Add creator's department if it exists
            if ($document->creator && $document->creator->department) {
                $departments->push([
                    'name' => $document->creator->department->name,
                    'code' => $document->creator->department->code,
                    'date' => $document->created_at,
                    'status' => 'completed'
                ]);
            }
            
            foreach ($statusLogs as $log) {
                if ($log->updatedBy && $log->updatedBy->department) {
                    $deptName = $log->updatedBy->department->name;
                    if (!$departments->contains('name', $deptName)) {
                        $departments->push([
                            'name' => $deptName,
                            'code' => $log->updatedBy->department->code,
                            'date' => $log->action_date,
                            'status' => 'completed'
                        ]);
                    }
                }
            }
            
            // Mark current department as active
            if ($document->department) {
                $currentDeptName = $document->department->name;
                $found = false;
                $departments = $departments->map(function($dept) use ($currentDeptName, &$found) {
                    if ($dept['name'] === $currentDeptName && !$found) {
                        $dept['status'] = 'active';
                        $found = true;
                    }
                    return $dept;
                });
            }
            
            // Calculate progress percentage
            $activeIndex = $departments->search(function($dept) {
                return $dept['status'] === 'active';
            });
            
            if ($activeIndex === false) {
                $activeIndex = $departments->count() - 1;
            }
            
            $progressPercent = $departments->count() > 1 
                ? (($activeIndex + 1) / $departments->count()) * 100 
                : 100;
        @endphp
        
        <!-- Department Journey -->
        <div class="department-journey">
            <div class="department-journey-title">
                <i class="bi bi-building"></i> Department Journey
            </div>
            <div class="journey-steps">
                <div class="journey-line"></div>
                <div class="journey-line-active" style="width: {{ $progressPercent }}%"></div>
                
                @foreach($departments as $index => $dept)
                <div class="journey-step {{ $dept['status'] }}">
                    <div class="journey-step-circle">
                        @if($dept['status'] === 'completed')
                            <i class="bi bi-check"></i>
                        @elseif($dept['status'] === 'active')
                            <i class="bi bi-arrow-right"></i>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    <div class="journey-step-label">
                        {{ $dept['code'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
                <!-- Timeline -->
                <div class="document-timeline">
                    <!-- Document Creation -->
                    <div class="timeline-item">
                        <div class="timeline-icon status-created">
                            <i class="bi bi-file-earmark-plus"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">
                                <span class="badge bg-primary">Document Created</span>
                            </div>
                            <div class="timeline-meta">
                                <span>by <strong>{{ $document->creator ? $document->creator->name : 'Unknown' }}</strong> 
                                @if($document->creator && $document->creator->department)
                                ({{ $document->creator->department->name }})
                                @endif
                                </span>
                            </div>
                            <div class="timeline-date">
                                {{ $document->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                    </div>
            
            <!-- Status Changes -->
            @foreach($statusLogs as $log)
            <div class="timeline-item">
                @php
                    // Determine icon based on status
                    $iconMap = [
                        'Pending' => 'bi-hourglass-split',
                        'Pending Verification' => 'bi-shield-exclamation',
                        'Received' => 'bi-inbox-fill',
                        'Under Review' => 'bi-search',
                        'Forwarded' => 'bi-arrow-right-circle-fill',
                        'Retrieved' => 'bi-arrow-counterclockwise',
                        'Approved' => 'bi-check-circle-fill',
                        'Rejected' => 'bi-x-circle-fill',
                        'Archived' => 'bi-archive-fill'
                    ];
                    
                    $icon = $iconMap[$log->new_status] ?? 'bi-circle-fill';
                    $statusClass = 'status-' . strtolower(str_replace(' ', '-', $log->new_status));
                @endphp
                
                <div class="timeline-icon {{ $statusClass }}">
                    <i class="{{ $icon }}"></i>
                </div>
                        <div class="timeline-content">
                            <div class="timeline-title">
                                @php
                                    // Check if this is a return action
                                    $isReturn = $log->new_status == 'Return';
                                    
                                    // Determine badge color - red for returns, otherwise use standard colors
                                    $badgeColor = 'info';
                                    if ($isReturn) {
                                        $badgeColor = 'danger';
                                    } elseif ($log->new_status == 'Approved') {
                                        $badgeColor = 'success';
                                    } elseif ($log->new_status == 'Completed') {
                                        $badgeColor = 'primary';
                                    } elseif ($log->new_status == 'Received') {
                                        $badgeColor = 'success';
                                    } elseif ($log->new_status == 'Retrieved') {
                                        $badgeColor = 'info';
                                    } elseif ($log->new_status == 'Pending' || $log->new_status == 'Pending Verification') {
                                        $badgeColor = 'warning';
                                    } elseif ($log->new_status == 'Rejected') {
                                        $badgeColor = 'danger';
                                    }
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}">
                                    {{ $log->new_status }}
                                </span>
                            </div>
                            <div class="timeline-meta">
                                @if($log->updatedBy && $log->updatedBy->department)
                                <span>by <strong>{{ $log->updatedBy->department->name }}</strong></span>
                                @else
                                <span>by <strong>{{ $log->updatedBy ? $log->updatedBy->name : 'System' }}</strong></span>
                                @endif
                            </div>
                            <div class="timeline-date">
                                {{ $log->action_date->format('M d, Y h:i A') }}
                            </div>
                    
                    @if($log->remarks && $isReturn)
                    <div class="timeline-remarks">
                        <i class="bi bi-chat-text"></i> {{ $log->remarks }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
            
                    <!-- Current Status (if not archived) -->
                    @if($document->status != 'Archived')
                    <div class="timeline-item">
                        <div class="timeline-icon status-current">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="timeline-content" style="background: #f8f9fa;">
                            <div class="timeline-title">
                                <span class="badge bg-primary">Current Location</span>
                                @if($document->is_priority)
                                <span class="badge badge-priority ms-1">PRIORITY</span>
                                @endif
                            </div>
                            <div class="timeline-meta">
                                <span>
                                    @if(in_array($document->status, ['Forwarded', 'Pending']))
                                    <strong class="text-muted">N/A</strong>
                                    @elseif($document->department)
                                    <strong>{{ $document->department->name }}</strong>
                                    @else
                                    <strong>Unassigned</strong>
                                    @endif
                                    @if($document->currentHandler)
                                    • {{ $document->currentHandler->name }}
                                    @endif
                                </span>
                            </div>
                            <div class="timeline-date">
                                Status: 
                                <span class="badge bg-{{ $document->status == 'Approved' ? 'success' : ($document->status == 'Received' ? 'success' : ($document->status == 'Retrieved' ? 'info' : ($document->status == 'Pending' || $document->status == 'Pending Verification' ? 'warning' : ($document->status == 'Rejected' ? 'danger' : 'info')))) }}">
                                    {{ $document->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif
        </div>
    </div>
</div>

