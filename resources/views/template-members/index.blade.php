@extends('layouts.app')

@section('content')
<legend class="text-primary">
  {{ _i('Email Templates') }}
  <a href="template-member/create" class="btn btn-primary float-right" data-toggle="modal" data-target="#ajaxModal" title="Document member" data-source="/template-member" data-resource="/template-member/create/">{{ _i('Create a new member of templates') }}</a>
</legend>
<div class="row">
  <div class="col">
    <div class="card border-primary">
      <table class="table table-striped table-hover table-sm">
        <thead>
          <tr id="filter" class="bg-primary text-light">
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/template-member" name="summary" placeholder="{{ _i('Summary') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/template-member" name="class" placeholder="{{ _i('Class') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/template-member" name="language" placeholder="{{ _i('Language') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/template-member" name="style" placeholder="{{ _i('Style') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/template-member" name="format" placeholder="{{ _i('Format') }}"></th>
            <th class="border-top-0"><input class="filter-input form-control form-control-sm" data-source="/template-member" name="category" placeholder="{{ _i('category') }}"></th>
          </tr>
        </thead>
        <tbody id="tableList">
          @foreach ($template_members as $member)
          <tr data-id="{{ $member->id }}" class="reveal-hidden">
            <td>
              <a href="/template-member/{{ $member->id }}" data-panel="ajaxPanel" title="{{ _i('Class data') }}">
                {{ $member->summary }}
              </a>
            </td>
            <td>{{ $member->class->name }}</td>
            <td>{{ $member->language }}</td>
            <td>{{ $member->style }}</td>
            <td>{{ $member->format }}</td>
            <td>{{ $member->category }}</td>
          </tr>
          @endforeach
          <tr>
            <td colspan="5">
              {{ $template_members->links() }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="card border-info">
      <div class="card-header bg-info text-light">
        {{ _i('Template member information') }}
      </div>
      <div class="card-body p-2" id="ajaxPanel">
        <div class="alert alert-info" role="alert">
          {{ _i('Click on member to view and edit details') }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
@include('tables.table-js')
@endsection
