@extends('layouts.app')

@section('content')
<legend class="text-primary">
  {{ _i('Rules') }}
  <a class="badge badge-pill badge-primary" href="https://github.com/jjdejong/phpip/wiki/Tables#task_rules" target="_blank">?</a>
  <a href="rule/create" class="btn btn-primary float-right" data-toggle="modal" data-target="#ajaxModal" title="Rule data" data-source="/rule" data-resource="/rule/create/">{{ _i('Create rule') }}</a>
</legend>
<div class="row">
  <div class="col">
    <div class="card border-primary">
      <table class="table table-striped table-hover table-sm">
        <thead>
          <tr id="filter" class="bg-primary text-light">
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/rule" name="Task" placeholder="{{ _i('Task') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/rule" name="Detail" placeholder="{{ _i('Detail') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/rule" name="Trigger" placeholder="{{ _i('Trigger event') }}" /></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/rule" name="Category" placeholder="{{ _i('Category') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/rule" name="Country" placeholder="{{ _i('Country') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/rule" name="Origin" placeholder="{{ _i('Origin') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/rule" name="Type" placeholder="{{ _i('Type') }}"></th>
          </tr>
        </thead>
        <tbody id="tableList">
          @foreach ($ruleslist as $rule)
          <tr data-id="{{ $rule->id }}" class="reveal-hidden">
            <td>
              <a href="/rule/{{ $rule->id }}" data-panel="ajaxPanel" title="{{ _i('Rule data') }}">
                {{ $rule->taskInfo->name }}
              </a>
            </td>
            <td>{{ $rule->detail }}</td>
            <td>{{ empty($rule->trigger) ? '' : $rule->trigger->name }}</td>
            <td>{{ empty($rule->category) ? '' : $rule->category->category }}</td>
            <td>{{ empty($rule->country) ? '' : $rule->country->name }}</td>
            <td>{{ empty($rule->origin) ? '' : $rule->origin->name }}</td>
            <td>{{ empty($rule->type) ? '' : $rule->type->type }}</td>
          </tr>
          @endforeach
          <tr>
            <td colspan="5">
              {{ $ruleslist->links() }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-4">
    <div class="card border-info">
      <div class="card-header bg-info text-light">
        {{ _i('Rule information') }}
      </div>
      <div class="card-body p-2" id="ajaxPanel">
        <div class="alert alert-info" role="alert">
          {{ _i('Click on rule to view and edit details') }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')

@include('tables.table-js')

@endsection
