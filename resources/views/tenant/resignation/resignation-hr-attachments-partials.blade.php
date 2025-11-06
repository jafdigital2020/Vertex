    @forelse ($attachments as $attachment)
 <tr class="text-center">
    <td>{{ $loop->iteration }}</td>
    <td style="max-width: 100px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; vertical-align: middle;">
        <a href="{{ asset('storage/resignation_attachments/' . basename( $attachment->filename)) }}"
        target="_blank"
        style="display: inline-block; width: 100%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-size: 10px;"
        title="{{  $attachment->filename }}">
            <i class="bi bi-file-earmark-text me-1 text-secondary"></i>
            {{ basename( $attachment->filename) }}
        </a>
    </td>
</tr>
    @empty
    <tr>
        <td colspan="2" class="text-center text-muted">No attachments uploaded yet.</td>
    </tr>
    @endforelse