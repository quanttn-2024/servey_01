<header>
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-xs-3" id="logo">
                <a href="{{ action('SurveyController@index') }}"></a>
            </div>
            <nav class="col-md-8 col-xs-9" id="top-nav">
                <ul>
                    <li>
                        <a href="{{ action('SurveyController@index') }}">
                            <span class="glyphicon glyphicon-home">
                            </span>
                            {{ trans('home.home') }}
                        </a>
                    </li>
                    @if (!Auth::guard()->check())
                        <li>
                            <a href="{{ action('Auth\LoginController@getLogin') }}">
                                <span class="glyphicon glyphicon-log-in span-menu">
                                </span>
                                {{ trans('login.login') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ action('Auth\RegisterController@getRegister') }}">
                                <span class="glyphicon glyphicon-registration-mark span-menu">
                                </span>
                                {{ trans('login.register') }}
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ action('SurveyController@listSurveyUser') }}">
                                <span class="glyphicon glyphicon-th">
                                </span>
                                {{ trans('home.list_survey') }}
                            </a>
                        </li>
                        <li>
                        <span>
                            {!! Html::image(Auth::user()->image, '', [
                                'class' => 'image-avatar',
                            ]) !!}
                         </span>
                            <a href="{{ action('User\UserController@show') }}">
                                {{ Auth::user()->part_name }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ action('Auth\LoginController@logout') }}">
                                <span class="glyphicon glyphicon-log-out">
                                </span>
                                {{ trans('login.logout') }}
                            </a>
                        </li>
                    @endif
                    <li class="li-lang">
                        <select name="locale" id="countries" data-url="{{ action('LanguageController@index') }}">
                            <option value='en' {{ Session::get('locale') == 'en' ? 'selected' : '' }}
                                data-image="{{ asset('bower/ms-Dropdown/images/msdropdown/icons/blank.gif') }}"
                                data-imagecss="flag england">
                                {{ config('settings.language.en') }}
                            </option>
                            <option value='vn' {{ Session::get('locale') == 'vn' ? 'selected' : '' }}
                                data-image="{{ asset('bower/ms-Dropdown/images/msdropdown/icons/blank.gif') }}"
                                data-imagecss="flag vn">
                                 {{ config('settings.language.vn') }}
                                </option>
                            <option value='jp' {{ Session::get('locale') == 'jp' ? 'selected' : '' }}
                                data-image="{{ asset('bower/ms-Dropdown/images/msdropdown/icons/blank.gif') }}"
                                data-imagecss="flag jp">
                                {{ config('settings.language.jp') }}
                            </option>
                        </select>
                    </li>
                </ul>
            </nav>
        </div>
     </div>
</header>
