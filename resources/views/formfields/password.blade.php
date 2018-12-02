@if(isset($dataTypeContent->{$row->field}))
    <br>
    <small>{{ __('iwan::form.field_password_keep') }}</small>
@endif
<input type="password"
       @if($row->required == 1 && !isset($dataTypeContent->{$row->field})) required @endif
       class="form-control"
       name="{{ $row->field }}"
       value="">
