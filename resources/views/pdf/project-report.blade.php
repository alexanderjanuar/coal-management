<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $project->name }} - Project Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Header styles */
        .header {
            padding: 10px 0;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .header h1 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0 0 5px 0;
            text-align: center;
        }

        .header p {
            font-size: 9px;
            color: #666;
            margin: 0;
            text-align: center;
        }

        .company-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #1a3353;
        }

        .company-tagline {
            font-size: 10px;
            color: #666;
        }

        /* Project summary section */
        .project-summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .project-summary th {
            background-color: #f5f5f5;
            padding: 6px 8px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border: 1px solid #ddd;
            width: 25%;
        }

        .project-summary td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }

        .project-name {
            font-weight: 600;
            font-size: 12px;
            color: #1a3353;
            text-align: center;
            background-color: #f5f5f5;
            padding: 8px;
            border: 1px solid #ddd;
            margin-bottom: 5px;
        }

        /* Team section */
        .team-section {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .team-section th {
            background-color: #f5f5f5;
            padding: 6px 8px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border: 1px solid #ddd;
            width: 50%;
        }

        .team-section td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }

        /* Section headers */
        .section-header {
            background-color: #f5f5f5;
            padding: 6px 8px;
            font-weight: bold;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            color: #1a3353;
        }

        /* Timeline tables */
        .timeline-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .timeline-table th {
            background-color: #f5f5f5;
            padding: 6px 8px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border: 1px solid #ddd;
        }

        .timeline-table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        /* Status indicators */
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-right: 5px;
            border-radius: 50%;
            vertical-align: middle;
        }

        .status-pending {
            background-color: #f59e0b;
        }

        .status-completed {
            background-color: #10b981;
        }

        .status-in_progress {
            background-color: #3b82f6;
        }

        .status-blocked {
            background-color: #ef4444;
        }

        /* Footer */
        .footer {
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 8px;
            color: #666;
            padding: 5px 0;
            border-top: 1px solid #eee;
        }

        .page-number:after {
            content: counter(page);
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }

        /* Activity history table */
        .activity-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .activity-table th {
            background-color: #f5f5f5;
            padding: 6px 8px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border: 1px solid #ddd;
        }

        .activity-table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        /* Utility classes */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .label {
            color: #666;
            font-weight: 600;
        }

        .priority-urgent {
            color: #ef4444;
            font-weight: bold;
        }

        .priority-normal {
            color: #3b82f6;
        }

        .priority-low {
            color: #6b7280;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="company-header">
            <div class="company-name">JASA KONSULTAN BORNEO</div>
            <div class="company-tagline">Professional Consulting Services</div>
        </div>
        <h1>PROJECT REPORT</h1>
        <p>Generated on {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Project Information -->
    <div class="project-name">{{ $project->name }}</div>

    <table class="project-summary">
        <tr>
            <th>Client</th>
            <td>{{ $project->client->name }}</td>
            <th>Due Date</th>
            <td>{{ $project->due_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Priority</th>
            <td class="priority-{{ strtolower($project->priority) }}">{{ ucfirst($project->priority) }}</td>
            <th>Type</th>
            <td>{{ ucfirst($project->type) }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td colspan="3">
                <span class="status-indicator status-{{ str_replace(' ', '_', strtolower($project->status)) }}"></span>
                {{ ucwords(str_replace('_', ' ', $project->status)) }}
            </td>
        </tr>
        @if($project->description)
        <tr>
            <th>Description</th>
            <td colspan="3">{{ $project->description }}</td>
        </tr>
        @endif
    </table>

    <!-- Project Team -->
    <div class="section-header">Project Team</div>
    <table class="team-section">
        <tr>
            <th>Role</th>
            <th>Assigned to</th>
        </tr>
        <tr>
            <td>Project Manager</td>
            <td>{{ $project->user ? $project->user->name : 'Unassigned' }}</td>
        </tr>
    </table>

    <!-- Project Timeline -->
    <div class="section-header">Project Steps</div>
    <table class="timeline-table">
        <tr>
            <th width="40%">Step</th>
            <th width="20%">Department</th>
            <th width="20%">Start Date</th>
            <th width="20%">End Date</th>
        </tr>
        @foreach($steps as $step)
        <tr>
            <td>
                <span class="status-indicator status-{{ str_replace(' ', '_', strtolower($step->status)) }}"></span>
                <strong>{{ $step->order }}. {{ $step->name }}</strong>
                <div style="margin-top: 3px; color: #666;">Priority: {{ ucfirst($step->priority) }}</div>
            </td>
            <td>{{ $step->department ?? 'All' }}</td>
            <td>{{ $step->created_at->format('d/m/Y') }}</td>
            <td>{{ $step->due_date ? $step->due_date->format('d/m/Y') : 'Ongoing' }}</td>
        </tr>
        @endforeach
    </table>

    <!-- Tasks Section -->
    <div class="section-header">Tasks</div>
    <table class="timeline-table">
        <tr>
            <th width="40%">Task</th>
            <th width="30%">Step</th>
            <th width="15%">Status</th>
            <th width="15%">Date</th>
        </tr>
        @foreach($steps as $step)
        @foreach($step->tasks as $task)
        <tr>
            <td>
                <strong>{{ $task->title }}</strong>
                @if($task->description)
                <div style="margin-top: 3px; color: #666; font-size: 9px;">{{ $task->description }}</div>
                @endif
            </td>
            <td>{{ $step->name }}</td>
            <td>
                <span class="status-indicator status-{{ str_replace(' ', '_', strtolower($task->status)) }}"></span>
                {{ ucwords(str_replace('_', ' ', $task->status)) }}
            </td>
            <td>{{ $task->created_at->format('d/m/Y') }}</td>
        </tr>
        @endforeach
        @endforeach
    </table>

    <!-- Required Documents -->
    <div class="section-header">Required Documents</div>
    <table class="timeline-table">
        <tr>
            <th width="40%">Document</th>
            <th width="30%">Step</th>
            <th width="15%">Status</th>
            <th width="15%">Submissions</th>
        </tr>
        @foreach($steps as $step)
        @if($step->requiredDocuments && $step->requiredDocuments->count() > 0)
        @foreach($step->requiredDocuments as $document)
        <tr>
            <td>
                <strong>{{ $document->name }}</strong>
                @if($document->description)
                <div style="margin-top: 3px; color: #666; font-size: 9px;">{{ $document->description }}</div>
                @endif
            </td>
            <td>{{ $step->name }}</td>
            <td>
                <span class="status-indicator status-{{ str_replace(' ', '_', strtolower($document->status)) }}"></span>
                {{ ucwords(str_replace('_', ' ', $document->status)) }}
            </td>
            <td class="text-center">
                @if($document->submittedDocuments && $document->submittedDocuments->count() > 0)
                {{ $document->submittedDocuments->count() }}
                @else
                0
                @endif
            </td>
        </tr>
        @endforeach
        @endif
        @endforeach
    </table>

    <div class="page-break"></div>

    <!-- Activity History -->
    <div class="section-header">Activity History</div>
    <table class="activity-table">
        <tr>
            <th width="15%">Date & Time</th>
            <th width="15%">Type</th>
            <th width="15%">Action</th>
            <th width="40%">Description</th>
            <th width="15%">User</th>
        </tr>
        @foreach($activities as $activity)
        @php
        $entityType = str_replace('App\\Models\\', '', $activity->subject_type ?? '');
        @endphp
        <tr>
            <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $entityType }}</td>
            <td>{{ ucfirst($activity->event) }}</td>
            <td>{{ str_replace(['was created', 'was updated', 'was deleted'], ['created', 'updated', 'deleted'],
                $activity->description) }}</td>
            <td>{{ $activity->causer ? $activity->causer->name : 'System' }}</td>
        </tr>
        @endforeach
    </table>

    <!-- Changes Table -->
    @if($activities->where('event', 'updated')->count() > 0)
    <div class="section-header">Detailed Changes</div>
    <table class="activity-table">
        <tr>
            <th width="20%">Date & Item</th>
            <th width="20%">Field</th>
            <th width="30%">Previous Value</th>
            <th width="30%">New Value</th>
        </tr>
        @foreach($activities->where('event', 'updated') as $activity)
        @if(count($activity->properties) > 0 && isset($activity->properties['attributes']))
        @php
        $entityType = str_replace('App\\Models\\', '', $activity->subject_type ?? '');
        $hasChanges = false;
        foreach($activity->properties['attributes'] as $key => $value) {
        if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] !== $value) {
        $hasChanges = true;
        break;
        }
        }
        @endphp

        @if($hasChanges)
        @foreach($activity->properties['attributes'] as $key => $value)
        @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] !== $value)
        <tr>
            <td>
                {{ $activity->created_at->format('d/m/Y H:i') }}<br>
                <small>{{ $entityType }}: {{ str_replace(['was created', 'was updated', 'was deleted'], ['created',
                    'updated', 'deleted'],
                    $activity->description) }}</small>
            </td>
            <td><strong>{{ ucfirst($key) }}</strong></td>
            <td>
                @if($key == 'status')
                <span
                    class="status-indicator status-{{ str_replace(' ', '_', strtolower($activity->properties['old'][$key])) }}"></span>
                {{ ucwords(str_replace('_', ' ', $activity->properties['old'][$key])) }}
                @else
                {{ is_array($activity->properties['old'][$key]) ?
                json_encode($activity->properties['old'][$key]) : $activity->properties['old'][$key] }}
                @endif
            </td>
            <td>
                @if($key == 'status')
                <span class="status-indicator status-{{ str_replace(' ', '_', strtolower($value)) }}"></span>
                {{ ucwords(str_replace('_', ' ', $value)) }}
                @else
                {{ is_array($value) ? json_encode($value) : $value }}
                @endif
            </td>
        </tr>
        @endif
        @endforeach
        @endif
        @endif
        @endforeach
    </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>JASA KONSULTAN BORNEO | Project Report: {{ $project->name }} | Generated: {{ now()->format('d/m/Y H:i') }} |
            Page <span class="page-number"></span></p>
    </div>
</body>

</html>