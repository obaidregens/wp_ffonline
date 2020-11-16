<?php
$story = $app->story;
$app->bundle = global_bundle('story-stats');
$app->bundle->css("css/components/index");
$app->bundle->css("css/components/collapsible");
$app->bundle->css("css/components/overview");
$app->bundle->css("css/views/book-stats");
$inst = book_stats::cached($story->ID);
$last_updated = (time() - $inst->updated <= 60*60) ? "Less than an hour ago" : human_time_diff( $inst->updated ) . " ago";
$chapters = published_chapters($story->ID,-1,'ids');
?>
<a href="/my-stories" class="back-to-stories"></a>
<input class="collapsible" type="checkbox">
<label>What are Reads?</label>
<collapsible>
<p>Reads are the most accurate way to measure a story's readership.</p>

<p>A single reader can have multiple reads, but the read count is not inflated if a reader refreshes the chapter, or in the case of stories, is still reading but continuing to the next chapter.</p>
<p>
    A read is recorded in two cases:
    <ul>
        <li>A reader reads a story/chapter they haven't read before.</li>
        <li>A reader returns to reading a story/chapter they've already read after a while.</li>
    </ul>
</p>
<p>
    <strong>Example:</strong>
</p>
<p>
Emma likes the summary of a a story she hasn't read before, and she starts reading the first chapter.
<br>
<em>This is recorded as a new read for the story, and the first chapter.</em>
</p>
<p>
Emma enjoys reading the story, and reads chapters 2 to 6.
<br>
<em>There is no increase in the story reads count, but a new read is recorded for each of chapter's 2 to 6.</em>
</p>
<p>
Emma goes to sleep, and reads chapter 7 in the morning.
<br>
<em>A new read is recorded for the story, as well as chapter 7.</em>
</p>
<p>
Because chapter reads are counted individually, and the story reads as a whole, the sum of all chapter reads will almost always be greater than the story reads.
</p>

</collapsible>
<last-updated>Last Updated: <?= $last_updated; ?></last-updated>
<h3>Story Reads</h3>
<overview>
<block label="This Week" count="<?= $inst->story->thisweek; ?>"></block>
<block label="All Time" count="<?= $inst->story->alltime; ?>"></block>
</overview>
<h3>Chapter Reads</h3>
<index>
    <li head>
        <cell>Chapter</cell>
        <cell>This Week</cell>
        <cell>All Time</cell>
    </li>
    <?php foreach($chapters as $chapter_id) { ?>
    <li>
        <cell class="counter"></cell>
        <cell><?= ($inst->chapters[$chapter_id]->thisweek) ?? 0; ?></cell>
        <cell><?= ($inst->chapters[$chapter_id]->alltime) ?? 0; ?></cell>
    </li>
    <?php } ?>
</index>