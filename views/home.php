<?php
$app->bundle = global_bundle('home');
$app->bundle->mix('glide_js');
$app->bundle->css('css/views/home-main');
$app->bundle->js('js/views/home-main');
$app->bundle->enqueue();
?>
<!-- Main -->
<section>
    <section-header>
    Hi
    </section-header>
    <section-description>
    Welcome to Fanfiction Online
    </section-description>
    <mini-description class="action">
    <a theme class="button" href="/read">Start Reading</a>
    <a theme class="button" href="/write">Start Writing</a>
    </mini-description>
</section>
<!-- Write -->
<section>
    <div class="glide">
        <div data-glide-el="controls">
            <button data-glide-dir="<"></button>
            <button data-glide-dir=">"></button>
        </div>
        <div class="glide__track" data-glide-el="track">
            <ul class="glide__slides">
                <li class="glide__slide">
                    <section-description>Write</section-description>
                    <mini-description>
                    Write. Share. Collaborate. Export. All from one place.
                    <br>
                    <a href="/drafts/new">Start Writing</a>
                    </mini-description>
                </li>
                <li class="glide__slide">
                    <section-description>Autosave</section-description>
                    <mini-description>
                    As you type, your drafts will be saved automatically so you never have to worry about losing your work.
                    <br>
                    <a href="/drafts/new">Start Writing</a>
                    </mini-description>
                </li>
                <li class="glide__slide">
                    <section-description>Versions</section-description>
                    <mini-description>
                    Did you prefer the previous version of your draft? Easily take your draft back to any point in the past.
                    <br>
                    <a href="/drafts/new">Start Writing</a>
                    </mini-description>
                </li>
                <li class="glide__slide">
                    <section-description>Collaborate</section-description>
                    <mini-description>
                    Collaborate with others, review their edits, and use them in your stories.
                    <br>
                    <a href="/drafts/new">Start Writing</a>
                    </mini-description>
                </li>
                <li class="glide__slide">
                    <section-description>Export</section-description>
                    <mini-description>
                    Write on Fanfiction Online. Publish everywhere.
                    <br>
                    <a href="/drafts/new">Start Writing</a>
                    </mini-description>
                </li>
            </ul>
        </div>
    </div>
</section>
<!-- Find Stories -->
<section>
    <section-description>
    Find Stories
    </section-description>
    <mini-description>
    Find stories you like easily with our robust filters. Filter by fandom, genre, characters, pairings, or anything at all!
    <br>
    <a href="/read">Start Reading</a>
    </mini-description>
</section>
<!-- Customize Reading -->
<section>
    <section-description>
    Customize Reading
    </section-description>
    <mini-description>
    Customize your reading experience and make your eyes happy! Change text size, themes, fonts and much more.
    <br>
    <a href="/read">Start Reading</a>
    </mini-description>
</section>
<!-- Customize Reading -->
<section>
    <section-description>
    Read Offline
    </section-description>
    <mini-description>
    Save your favorite stories and read them without internet!
    <br>
    <a href="/read">Start Reading</a>
    </mini-description>
</section>
<!-- Collections -->
<section>
    <section-description>
    Collections
    </section-description>
    <mini-description>
    Wish you could save stories to read later? Share with everyone the ones you love? Easily add a story to your collections with a single tap.
    <br>
    <a href="/read">Start Reading</a>
    </mini-description>
</section>
<!-- Why -->
<section>
    <section-description>Why?</section-description>
    <mini-description>
    Fanfiction Online was created to cater to the needs of fanfiction writers & readers. We wanted to provide a platform that helps writers promote their work and encourage readership by making their stories more accessible & reading them easier.
    </mini-description>
</section>
<!-- Contact -->
<section>
    <section-description>Have any questions?</section-description>
    <mini-description><a href="/faq">Check out our FAQ's</a></mini-description>
</section>
<section>
    <section-description>
    <a href="/read">Get Started</a>
    </section-description>
    </mini-description>
</section>