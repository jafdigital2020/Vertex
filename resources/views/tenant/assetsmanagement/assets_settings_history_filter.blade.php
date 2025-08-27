@foreach ($assetsHistory as $asset)
    <tr class="text-center">
        <td>{{$asset->name}}</td>
        <td>{{$asset->item_name}}</td>
        <td>{{$asset->branch->name}}</td>
        <td>{{$asset->description}}</td>
        <td>{{$asset->category->name}}</td>
        <td>{{$asset->assetsDetails->count() }}</td>
        <td>{{$asset->price}}</td>
        <td>{{$asset->serial_number}}</td>
        <td>{{$asset->processor}}</td>
        <td>{{$asset->model}}</td>
        <td>{{$asset->manufacturer}}</td> 
        <td>{{$asset->process}}</td>
        <td>{{$asset->updatedBy->personalInformation->first_name ?? ''}} {{$asset->updatedBy->personalInformation->last_name ?? ''}}</td>
        <td>{{ $asset->updated_at ?? '' }}</td>
        <td>{{$asset->createdBy->personalInformation->first_name ?? ''}} {{$asset->createdBy->personalInformation->last_name ?? ''}}</td>
        <td>{{ $asset->created_at ?? '' }}</td>
    </tr>
@endforeach 