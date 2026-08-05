@if ($paginator->hasPages())
  <nav class="mem-pagination" role="navigation" aria-label="Pagination">
    {{-- Previous Page --}}
    @if ($paginator->onFirstPage())
      <span class="mem-page mem-page-disabled" aria-disabled="true"><i class="fas fa-chevron-left"></i></span>
    @else
      <a class="mem-page" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="fas fa-chevron-left"></i></a>
    @endif

    {{-- Page Numbers --}}
    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="mem-page mem-page-disabled">{{ $element }}</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="mem-page mem-page-active" aria-current="page">{{ $page }}</span>
          @else
            <a class="mem-page" href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach

    {{-- Next Page --}}
    @if ($paginator->hasMorePages())
      <a class="mem-page" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="fas fa-chevron-right"></i></a>
    @else
      <span class="mem-page mem-page-disabled" aria-disabled="true"><i class="fas fa-chevron-right"></i></span>
    @endif
  </nav>
@endif
