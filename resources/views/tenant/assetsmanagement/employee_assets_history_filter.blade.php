   @foreach ($assetsHistory as $asset)
      <tr class="text-center">
          <td>{{ $asset->assetDetail->assets->name ?? '' }}</td>
          <td>{{ $asset->assetDetail->assets->item_name ?? '' }}</td>
          <td>{{$asset->assetDetail->assets->branch->name ?? ''}}</td>
          <td>{{ $asset->assetDetail->assets->category->name ?? '' }}</td>
          <td>{{ $asset->item_no ?? '' }}</td>
          <td>{{ $asset->deployedToEmployee->personalInformation->first_name ?? '' }} {{ $asset->deployedToEmployee->personalInformation->last_name ?? '' }} </td>
          <td>{{ $asset->deployed_date ?? '' }}</td>
          <td>{{ $asset->condition ?? '' }}</td>
          <td>{{ $asset->condition_remarks ?? '' }}</td>
          <td>{{ $asset->status ?? '' }}</td>
          <td>{{ $asset->process ?? '' }}</td>
          <td>{{ $asset->updatedByUser->personalInformation->first_name ?? '' }} {{ $asset->updatedByUser->personalInformation->last_name ?? '' }}</td>
          <td>{{ $asset->updated_at ?? '' }}</td>
          <td>{{ $asset->createdByUser->personalInformation->first_name ?? '' }} {{ $asset->createdByUser->personalInformation->last_name ?? '' }}</td>
          <td>{{ $asset->created_at ?? '' }}</td>
      </tr>
      @endforeach 