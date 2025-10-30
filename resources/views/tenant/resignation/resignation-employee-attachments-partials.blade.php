@php
$counter = 1;
@endphp
@forelse ($myUploads as $index => $file)
<tr class="text-xs">
<td  class="text-center" >{{  $counter++ }}</td>
<td style="max-width: 300px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; vertical-align: middle;">
<a href="{{ asset('storage/resignation_attachments/' . basename($file->filename)) }}"
target="_blank"
style="display: inline-block; width: 100%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-size: 10px;"
title="{{ $file->filename }}">
<i class="bi bi-file-earmark-text me-1 text-secondary"></i>
{{ basename($file->filename) }}
</a>
</td>

<td  class="text-center" >
@if ($file->status === 'approved')
<span class="badge bg-success">Approved</span>
@elseif ($file->status === 'pending')
<span class="badge bg-warning text-dark">Pending</span>
@elseif ($file->status === 'rejected')
<span class="badge bg-danger">Rejected</span>
@else
<span class="badge bg-secondary">Unknown</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="4" class="text-center text-muted">No attachments uploaded yet.</td>
</tr>
@endforelse