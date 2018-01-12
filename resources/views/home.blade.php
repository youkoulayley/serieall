@extends('layouts.app')

@section('pageTitle', 'Accueil')
@section('pageDescription', 'Webzine communautaire des séries TV - Critiques et actualité des séries tv, notez et laissez vos avis sur les derniers épisodes, créez votre planning ...')

@section('content')
    <div class="slider">
        <div class="slider-wrapper flex">
            <div class="slide flex">
                <div class="slide-image slider-link prev">
                    <img src="https://goranvrban.com/codepen/img2.jpg">
                    <div class="overlay"></div>
                </div>
                <div class="slide-content">
                    <div class="slide-date">30.07.2017.</div>
                    <div class="slide-title">LOREM IPSUM DOLOR SITE MATE, AD EST ABHORREANT</div>
                    <div class="slide-text">Lorem ipsum dolor sit amet, ad est abhorreant efficiantur, vero oporteat apeirian in vel. Et appareat electram appellantur est. Ei nec duis invenire. Cu mel ipsum laoreet, per rebum omittam ex. </div>
                    <div class="slide-more">READ MORE</div>
                </div>
            </div>
            <div class="slide flex">
                <div class="slide-image slider-link next"><img src="https://goranvrban.com/codepen/img3.jpg"><div class="overlay"></div></div>
                <div class="slide-content">
                    <div class="slide-date">30.08.2017.</div>
                    <div class="slide-title">LOREM IPSUM DOLOR SITE MATE, AD EST ABHORREANT</div>
                    <div class="slide-text">Lorem ipsum dolor sit amet, ad est abhorreant efficiantur, vero oporteat apeirian in vel. Et appareat electram appellantur est. Ei nec duis invenire. Cu mel ipsum laoreet, per rebum omittam ex. </div>
                    <div class="slide-more">READ MORE</div>
                </div>
            </div>
            <div class="slide flex">
                <div class="slide-image slider-link next"><img src="https://goranvrban.com/codepen/img5.jpg"><div class="overlay"></div></div>
                <div class="slide-content">
                    <div class="slide-date">30.09.2017.</div>
                    <div class="slide-title">LOREM IPSUM DOLOR SITE MATE, AD EST ABHORREANT</div>
                    <div class="slide-text">Lorem ipsum dolor sit amet, ad est abhorreant efficiantur, vero oporteat apeirian in vel. Et appareat electram appellantur est. Ei nec duis invenire. Cu mel ipsum laoreet, per rebum omittam ex. </div>
                    <div class="slide-more">READ MORE</div>
                </div>
            </div>
            <div class="slide flex">
                <div class="slide-image slider-link next"><img src="https://goranvrban.com/codepen/img6.jpg"><div class="overlay"></div></div>
                <div class="slide-content">
                    <div class="slide-date">30.10.2017.</div>
                    <div class="slide-title">LOREM IPSUM DOLOR SITE MATE, AD EST ABHORREANT</div>
                    <div class="slide-text">Lorem ipsum dolor sit amet, ad est abhorreant efficiantur, vero oporteat apeirian in vel. Et appareat electram appellantur est. Ei nec duis invenire. Cu mel ipsum laoreet, per rebum omittam ex. </div>
                    <div class="slide-more">READ MORE</div>
                </div>
            </div>
        </div>
        <div class="arrows">
            <a href="#" title="Previous" class="arrow slider-link prev"></a>
            <a href="#" title="Next" class="arrow slider-link next"></a>
        </div>
    </div>
@endsection
