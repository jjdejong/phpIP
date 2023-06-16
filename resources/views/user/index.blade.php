@extends('layouts.app')

@section('content')
<legend class="text-primary">
    {{ _i('Users') }}
    <a href="user/create" class="btn btn-primary float-right" data-toggle="modal" data-target="#ajaxModal" title="{{ _i('Create User') }}">{{ _i('Create user') }}</a>
</legend>
<div class="row">
  <div class="col">
    <div class="card border-primary">
      <table class="table table-striped table-hover table-sm col">
        <thead class="card-header">
          <tr id="filterFields" class="bg-primary text-light">
            <th class="border-top-0"><input class="form-control form-control-sm" name="Name" placeholder="{{ _i('Name') }}" value="{{ Request::get('Name') }}"></th>
            <th class="align-middle border-top-0">{{ _i('Role') }}</th>
            <th class="align-middle border-top-0">{{ _i('Login') }}</th>
            <th class="align-middle border-top-0">{{ _i('Company') }}</th>
          </tr>
        </thead>
        <tbody id="userList" class="card-body">
          @foreach ($userslist as $user)
          <tr class="reveal-hidden" data-id="{{ $user->id }}">
            <td>
              <a @if($user->warn) class="text-danger text-decoration-none" @endif href="/user/{{ $user->id }}" data-panel="ajaxPanel" title="{{ _i('User data') }}">
                {{ $user->name }}
              </a>
            </td>
            <td>{{ $user->default_role }}</td>
            <td>{{ $user->login }}</td>
            <td>{{ empty($user->company) ? '' : $user->company->name }}</td>
          </tr>
          @endforeach
          <tr>
            <td colspan="5">
              {{ $userslist->links() }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-4">
    <div class="card border-info">
      <div class="card-header bg-info text-light">
        {{ _i('User information') }}
      </div>
      <div class="card-body p-2" id="ajaxPanel">
        <div class="alert alert-info" role="alert">
          {{ _i('Click on user name to view and edit details') }}
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script>

  var url = new URL(window.location.href);

  function refreshUserList() {
    window.history.pushState('', 'phpIP', url)
    reloadPart(url, 'userList');
  }

  filterFields.addEventListener('input', debounce( e => {
    if (e.target.value.length === 0) {
      url.searchParams.delete(e.target.name);
    } else {
      url.searchParams.set(e.target.name, e.target.value);
    }
    refreshUserList();
  }, 300));

</script>
@endsection
