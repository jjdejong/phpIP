<form id="createClassifierTypeForm">
  <fieldset>
    <table class="table table-sm">
      <tr>
        <td><label for="code" title="{{ $tableComments['code'] }}"><b>{{ _i('Code') }}</b></label></td>
        <td><input type="text" class="form-control form-control-sm" name="code"></td>
        <td><label for="type" title="{{ $tableComments['type'] }}"><b>{{ _i('Type') }}</b></label></td>
        <td><input type="text" class="form-control form-control-sm" name="type"></td>
      </tr>
      <tr>
        <td><label for="display_order" title="{{ $tableComments['display_order'] }}">{{ _i('Display order') }}</label></td>
        <td><input type="text" class="form-control form-control-sm" name="display_order"></td>
        <td><label for="for_category" title="{{ $tableComments['for_category'] }}">{{ _i('Category') }}</label></td><td>
          <input type='hidden' name='for_category'>
          <input type="text" class="form-control form-control-sm" data-ac="/category/autocomplete" data-actarget="for_category" autocomplete="off">
        </td>
      </tr><tr>
        <td><label for="main_display" title="{{ $tableComments['main_display'] }}">{{ _i('Main display') }}</label></td>
          <td><input type="checkbox" class="form-control form-control-sm" value="1" name="main_display"></td>
        <td><label for="type" title="{{ $tableComments['notes'] }}">{{ _i('Notes') }}</label></td>
        <td><textarea class="form-control form-control-sm" name="notes"></textarea></td>
      </tr>
    </table>
  </fieldset>
  <button type="button" id="createClassifierTypeSubmit" class="btn btn-primary">{{ _i('Create type') }}</button><br>
</form>
