@foreach ($assets as $asset)
                                            <tr>  
                                                <td>{{ $asset->name ?? null }}</span>
                                                </td>
                                                <td>{{$asset->branch->name ?? null}}</td>
                                                 <td>{{$asset->description}}</td>
                                                  <td class="text-center">
                                                    {{ $asset->category->name ?? 'NA' }}
                                                </td> 
                                                <td class="text-center">
                                                    {{ $asset->model ?? 'NA' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $asset->manufacturer ?? 'NA' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $asset->serial_number ?? 'NA' }}
                                                </td>
                                                   <td class="text-center">
                                                    {{ $asset->processor ?? 'NA' }}
                                                </td>
                                                <td class="text-center">{{ $asset->assetsDetails->count() }}</td> 
                                                 <td class="text-center">
                                                    {{$asset->price}}
                                                </td>
                                                @if (in_array('Update', $permission))
                                                    <td class="text-center">
                                                        <div class="action-icon d-inline-flex">
                                                            @if(in_array('Update',$permission))
                                                            <a href="#" class="me-2" data-bs-toggle="modal"
                                                                data-bs-target="#edit_assetsCondition" data-id="{{ $asset->id }}" data-name="{{$asset->name}}" data-category="{{$asset->category->name}}"><i class="ti ti-tools"></i></a>
                                                            <a href="#" class="me-2" data-bs-toggle="modal"
                                                                data-bs-target="#edit_assets" data-id="{{ $asset->id }}" 
                                                                data-name="{{$asset->name}}" data-description="{{$asset->description}}" 
                                                                data-quantity="{{$asset->quantity}}" data-categoryname="{{$asset->category->id}}" 
                                                                data-price="{{$asset->price}}" data-status="{{$asset->status}}"
                                                                data-model="{{$asset->model}}" data-manufacturer="{{$asset->manufacturer}}" data-serial_number="{{$asset->serial_number}}" data-processor="{{$asset->processor}}"><i
                                                                    class="ti ti-edit"></i></a>
                                                            @endif
                                                            @if(in_array('Delete',$permission))
                                                            <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                                data-bs-target="#delete_assets" data-id="{{ $asset->id }}"
                                                                data-name="{{ $asset->name }}"><i
                                                                    class="ti ti-trash"></i></a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach