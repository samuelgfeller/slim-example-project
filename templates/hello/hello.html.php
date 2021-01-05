
<!--https://samuel-gfeller.ch/favicon.ico-->
<!--<img src="/assets/hello/slim-icon.png" alt="favicon">-->
<pre></pre>
<!--<ul>
    <li><a href="{{ url_for('hello', { 'name': 'Josh' }) }}"
           {% if is_current_url('hello', { 'name': 'Josh' }) %}class="active"{% endif %}>Josh</a></li>
    <li><a href="{{ url_for('hello', { 'name': 'Samuel' }) }}"
           {% if is_current_url('hello', { 'name': 'Samuel' }) %}class="active"{% endif %}>Samuel</a></li>
    <li><a href="{{ url_for('hello', { 'name': 'Patrick' }) }}"
           {% if is_current_url('hello', { 'name': 'patrick' }) %}class="active"{% endif %}>Patrick</a></li>
</ul>
-->
<h1>Hello <?= $name ?>!</h1>
<p>You have new notification(s).</p>
<!--<a href="{{ url_for('login-page') }}">Login page</a>-->
<?php
$ini = ini_get('error_reporting');
//var_dump($ini) ?>
