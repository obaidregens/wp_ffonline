<?php
$app->bundle = global_bundle('home');
$app->bundle->css('external/onepagescroll/onepagescroll');
$app->bundle->js('external/onepagescroll/onepagescroll');
$app->bundle->mix('glide_js');
$app->bundle->css('css/views/home-main');
$app->bundle->js('js/views/home-main');
$app->bundle->enqueue();
?>
<section>
<section-header>
Hi
</section-header>
<section-description>
Welcome to Fanfiction Online
</section-description>
<mini-description>
Scroll down to see more
</mini-description>
</section>
<section>
    <div class="glide">
        <div data-glide-el="controls">
            <button data-glide-dir="<"></button>
            <button data-glide-dir=">"></button>
        </div>
        <div class="glide__track" data-glide-el="track">
            <ul class="glide__slides">
                <li class="glide__slide">
                    <section-description>Drafts</section-description>
                    <mini-description>
                    Write. Share. Collaborate. Export. All from one place.
                    <br>
                    <a href="/drafts">Get started</a>
                    </mini-description>
                </li>
                <li class="glide__slide">
                    <section-description>Autosave</section-description>
                    <mini-description>
                    As you type, your drafts will be saved automatically so you never have to worry about losing your work.
                    <br>
                    <a href="/drafts">Get started</a>
                    </mini-description>
                </li>
                <li class="glide__slide">
                    <section-description>Collaborate</section-description>
                    <mini-description>
                    Collaborate with others, review thir edits, and use them in your stories.
                    <br>
                    <a href="/drafts">Get started</a>
                    </mini-description>
                </li>
                <li class="glide__slide">
                    <section-description>Export</section-description>
                    <mini-description>
                    Write on Fanfiction Online. Publish everywhere.
                    <br>
                    <a href="/drafts">Get started</a>
                    </mini-description>
                </li>
            </ul>
        </div>
    </div>
</section>
<section>
    <section-description>Why?</section-description>
    <mini-description>
    Fanfiction Online was created to cater to the needs of fanfiction writers & readers. I wanted to provide a platform that helps writers promote their work and encourage readership by making their stories more accessible & reading them easier.
    </mini-description>
</section>
<section>
    <section-description>Have any suggestions?</section-description>
    <mini-description><a href="/contact">Contact us</a></mini-description>
</section>
