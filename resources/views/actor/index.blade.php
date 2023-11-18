@extends('layouts.app')

@section('content')
<legend class="alert alert-dark d-flex justify-content-between py-2 mb-1">
    Actors
    <a href="actor/create" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajaxModal" title="Create Actor">Create actor</a>
</legend>
<div class="row">
  <div class="col">
    <div class="card border-primary p-1">
      <table class="table table-striped table-hover table-sm">
        <thead>
          <tr id="filter" class="table-primary align-middle">
            <th><input class="form-control" name="Name" placeholder="Name" value="{{ Request::get('Name') }}"></th>
            <th>First name</th>
            <th>Display name</th>
            <th class="text-center">Company <span class="float-end">Person</span></th>
            <th>
              <select id="person" class="form-select form-select-sm px-0" name="selector">
                <option value="" selected>All</option>
                <option value="phy_p">Physical</option>
                <option value="leg_p">Legal</option>
                <option value="warn">Warn</option>
              </select>
            </th>
          </tr>
        </thead>
        <tbody id="tableList">
          @foreach ($actorslist as $actor)
          <tr class="reveal-hidden" data-id="{{ $actor->id }}">
            <td>
              <a @if($actor->warn) class="text-danger text-decoration-none" @endif href="/actor/{{ $actor->id }}" data-panel="ajaxPanel" title="{{ _i('Actor data') }}">
                {{ $actor->name }}
              </a>
            </td>
            <td>{{ $actor->first_name }}</td>
            <td>{{ $actor->display_name }}</td>
            <td nowrap>{{ empty($actor->company) ? '' : $actor->company->name }}</td>
            <td>
              @if ($actor->phy_person)
              {{ _i("Physical") }}
              @else
              {{ _i("Legal") }}
              @endif
            </td>
          </tr>
          @endforeach
          <tr>
            <td colspan="5">
              {{ $actorslist->links() }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-4">
    <div class="card border-info">
      <div class="card-header bg-info text-light">
        {{ _i("Actor information") }}
      </div>
      <div class="card-body p-2" id="ajaxPanel">
        <div class="alert alert-info" role="alert">
          {{ _i("Click on actor name to view and edit details") }}
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script src="{{ asset('js/tables.js') }}" defer></script>
<script>
  person.onchange = (e) => {
    if (e.target.value.length === 0) {
      url.searchParams.delete(e.target.name);
    } else {
      url.searchParams.set(e.target.name, e.target.value);
    }
    refreshList();
  }
</script>
@endsection
