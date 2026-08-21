@php
    $value = strtolower((string) ($status ?? 'pending'));
    $labels = [
        'submitted' => 'Pending', 'under_review' => 'Pending', 'pending' => 'Pending',
        'approved' => 'Approved', 'rejected' => 'Rejected',
        'changes_requested' => 'Changes Requested', 'active' => 'Active',
        'inactive' => 'Inactive', 'draft' => 'Draft',
    ];
@endphp
<span class="bw-review-status bw-review-status--{{ $value }}">
    <span class="bw-review-status__dot"></span>{{ $prefix ?? '' }}{{ $labels[$value] ?? ucwords(str_replace('_', ' ', $value)) }}
</span>
