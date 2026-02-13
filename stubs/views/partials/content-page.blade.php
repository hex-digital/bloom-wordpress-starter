<div class="block-content">
  @php(the_content())
</div>

@if ($pagination)
  <nav class="page-nav" aria-label="Page">
    {!! $pagination !!}
  </nav>
@endif
