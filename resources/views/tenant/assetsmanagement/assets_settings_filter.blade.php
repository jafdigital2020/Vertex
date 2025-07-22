@foreach ($assets as $asset)
                                            <tr>  
                                                <td>{{ $asset->name ?? null }}</span>
                                                </td>
                                                <td class="text-center">
                                                    {{ $asset->description ?? 'NA' }}
                                                </td>
                                               <td class="text-center">
                                                    {{$asset->category->name }}
                                                </td>
                                               <td class="text-center">
                                                    {{$asset->price}}
                                                </td>
                                               <td class="text-center">
                                                    @php
                                                        $statusColors = [
                                                            'active' => 'success',
                                                            'broken' => 'danger',
                                                            'maintenance' => 'warning',
                                                            'retired' => 'secondary',
                                                        ];
                                                        $color = $statusColors[$asset->status ?? 'retired'] ?? 'secondary';
                                                    @endphp

                                                    <span class="badge bg-{{ $color }} text-capitalize">
                                                        {{ $asset->status ?? 'retired' }}
                                                    </span>
                                                </td>

                                                @if (in_array('Update', $permission))
                                                    <td class="text-center">
                                                        <div class="action-icon d-inline-flex">
                                                            @if(in_array('Update',$permission))
                                                            <a href="#" class="me-2" data-bs-toggle="modal"
                                                                data-bs-target="#edit_assets" data-id="{{ $asset->id }}" 
                                                                data-name="{{$asset->name}}" data-description="{{$asset->description}}" data-quantity="{{$asset->quantity}}" data-categoryname="{{$asset->category->id}}" data-price="{{$asset->price}}" data-status="{{$asset->status}}"><i
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