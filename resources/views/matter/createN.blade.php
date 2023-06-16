<form id="natMatterForm" class="ui-front">
	<input type="hidden" name="caseref" value="{{ $parent_matter->caseref }}" />
	<input type="hidden" name="category_code" value="{{ $parent_matter->category_code }}" />
	<input type="hidden" name="origin" value="{{ $parent_matter->country }}" />
	<input type="hidden" name="type_code" value="{{ $parent_matter->type_code }}" />
	<input type="hidden" name="idx" value="{{ $parent_matter->idx }}" />
	<input type="hidden" name="parent_id" value="{{ $parent_matter->id }}" />
	<input type="hidden" name="responsible" value="{{ $parent_matter->responsible }}" />
	<div id="ncountries">
		@foreach( $parent_matter->countryInfo->natcountries as $iso => $name )
		<div class="input-group" id="country-{{ $iso }}">
			<input type="hidden" name="ncountry[]" value="{{ $iso }}" />
			<input type="text" class="form-control" readonly value="{{ $name }}" />
			<div class="input-group-append">
				<button class="btn btn-outline-danger" type="button" id="{{ $iso }}" title="{{ _i('Remove'). ' ' . $iso }}">&times;</button>
			</div>
		</div>
		@endforeach
	</div>
	<div class="input-group">
		<input type="text" class="form-control" placeholder="{{ _i('Add country') }}" data-ac="/country/autocomplete" id="addCountry">
		<div class="input-group-append">
			<span class="input-group-text">&plus;</span>
		</div>
	</div>
	<button type="button" class="btn btn-primary btn-block mt-2" id="nationalizeSubmit">{{ _i("Submit") }}</button>
</form>

<template id="appendCountryTemplate">
	<div class="input-group">
		<input type="hidden" name="ncountry[]">
		<input type="text" class="form-control" value="" readonly>
		<div class="input-group-append">
			<button class="btn btn-outline-danger" type="button">&times;</button>
		</div>
	</div>
</template>
