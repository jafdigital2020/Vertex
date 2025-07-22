                                @foreach ($users as $user)
                                            <tr> 
                                                <td>
                                                    <div class="d-flex align-items-center file-name-icon">
                                                        <a href="#" class="avatar avatar-md avatar-rounded">
                                                            <img src="{{ URL::asset('build/img/users/user-32.jpg') }}"
                                                                class="img-fluid" alt="img">
                                                        </a>
                                                        <div class="ms-2">
                                                            <h6 class="fw-medium"><a
                                                                    href="#">{{ $user->personalInformation->first_name ?? '' }}
                                                                    {{ $user->personalInformation->last_name ?? '' }} </a></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $user->email }}</td>    
                                                @if (in_array('Update', $permission))
                                                    <td class="text-center">
                                                        <div class="action-icon d-inline-flex">
                                                            <a href="#" class="me-2" onclick='addEmployeeAssets(@json($user))'>
                                                                <i class="ti ti-edit"></i>
                                                            </a>
                                                        </div> 
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                  