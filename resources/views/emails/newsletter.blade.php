@extends('emails.layout')

@section('content')
    <div class="newsletter-content" style="color: #374151; line-height: 1.8; font-size: 16px;">
        {!! $content !!}
    </div>
    
    @if(isset($unsubscribe_url))
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #edf2f7; text-align: center;">
            <a href="{{ $unsubscribe_url }}" style="color: #9ca3af; text-decoration: none; font-size: 12px; font-style: italic;">
                Unsubscribe from our newsletter
            </a>
        </div>
    @endif
@endsection
