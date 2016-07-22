<div class="well">
    <h4>{{ $label }}</h4>
    @include(AdminTemplate::getViewPath('form.partials.elements'), ['items' => $items])
</div>