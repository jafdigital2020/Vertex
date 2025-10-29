@forelse ($attachments as $attachment)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>
        <a href="{{ asset('storage/resignation_attachments/' . $attachment->filename) }}"
           target="_blank"
           class="text-decoration-none text-primary fw-semibold text-truncate d-inline-block"
           style="max-width: 250px;"
           title="{{ $attachment->filename }}">
           <i class="bi bi-file-earmark-text me-1 text-secondary"></i>
           {{ $attachment->filename }}
        </a>
        <br>
        <small class="text-muted">{{ strtoupper($attachment->filetype ?? 'FILE') }}</small>
    </td>
</tr>
@empty
<tr>
    <td colspan="2" class="text-center text-muted">No attachments uploaded yet.</td>
</tr>
@endforelse
