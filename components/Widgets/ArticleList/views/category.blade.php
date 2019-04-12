<option value="category.{{$category['id']}}" style="padding-left: {{$depth*15}}px"  @if(in_array("category.".$category['id'], $boardId) ) selected="selected" @endif > ㄴ{{$category['name']}}</option>
@foreach($category['children'] as $child)
    @include('board::components.Widgets.ArticleList.views.category', ['category'=>$child,'depth'=>$depth+1])
@endforeach
