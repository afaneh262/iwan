<footer class="app-footer">
    <div class="site-footer-right">
        @if (rand(1,100) == 100)
            <i class="iwan-rum-1"></i> {{ __('iwan::theme.footer_copyright2') }}
        @else
            {!! __('iwan::theme.footer_copyright') !!} <a href="http://github.com/afaneh262" target="_blank">Wajid Afaneh</a>
        @endif
        @php $version = Iwan::getVersion(); @endphp
        @if (!empty($version))
            - {{ $version }}
        @endif
    </div>
</footer>
