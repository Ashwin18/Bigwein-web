<div class="bw-review-summary">
    @foreach($items as $item)
        <a href="{{ $item['url'] }}" class="bw-review-summary__card {{ !empty($item['active']) ? 'is-active' : '' }}">
            <span class="bw-review-summary__icon bw-review-summary__icon--{{ $item['tone'] ?? 'neutral' }}"><i class="bi {{ $item['icon'] ?? 'bi-inbox' }}"></i></span>
            <span><strong>{{ number_format($item['count'] ?? 0) }}</strong><small>{{ $item['label'] }}</small></span>
        </a>
    @endforeach
</div>
