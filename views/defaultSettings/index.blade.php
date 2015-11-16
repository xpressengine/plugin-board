<table class="table">
    <caption>Board Manager</caption>
    <thead>
    <tr>
        <th>#</th>
        <th>Board ID</th>
        <th>Name</th>
        <th>Config</th>
    </tr>
    </thead>
    <tbody>
    @foreach($configs as $config)
    <tr>
        <th scope="row">#</th>
        <td>{{ $config->get('boardId') }}</td>
        <td>{{ $config->get('boardName') }}</td>
        <td><a href="/{{$baseUrl}}/dynamicField/{{ $config->get('boardId') }}">config</a></td>
    </tr>
    @endforeach
    </tbody>
</table>
