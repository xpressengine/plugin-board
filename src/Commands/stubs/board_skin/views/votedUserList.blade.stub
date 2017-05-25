@foreach($paginate as $log)
    @if($log->user->getId() == Auth::user()->getId())
        <li>
            <!--[D] 링크가 아닌 경우 div 로 교체 -->
            <div>
                <!--[D] 실제 이미지 사이즈는 모바일 대응 위해 일대일 비율로 96*96 이상-->
                <div class="img-thumbnail"><img src="{{$log->user->getProfileImage()}}" width="48" height="48" alt="{{$log->user->getDisplayName()}}"></div>
                <div class="list-text">
                    <p>{{$log->user->getDisplayName()}}</p>
                </div>
            </div>
        </li>
    @else
        <li>
            <a href="#">
                <div class="img-thumbnail"><img src="{{$log->user->getProfileImage()}}" width="48" height="48" alt="{{$log->user->getDisplayName()}}"></div>
                <div class="list-text">
                    <p>{{$log->user->getDisplayName()}}</p>
                    <span class="sub-text" data-xe-timeago="{{ $item->createdAt }}">{{$log->createdAt}}</span>
                </div>
            </a>
        </li>
    @endif
@endforeach
