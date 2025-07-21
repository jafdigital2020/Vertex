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
            <td class="text-center">
            @if(($user->payrollBatchUsers ?? collect())->count())
                @foreach($user->payrollBatchUsers as $pbUser)
                    {{ $pbUser->batchSetting->name }}@if(!$loop->last), @endif
                @endforeach
            @else
                No Defined Batch
            @endif
            </td> 
            <td class="text-center">   
            <a href="#" onclick='editPayrollBatchUsers({{ $user->id }}, @json($user->payrollBatchUsers ?? []))'> 
                <i class="ti ti-edit"></i>
            </a>
            </td>   
        </tr>
    @endforeach