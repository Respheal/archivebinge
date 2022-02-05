<?php
require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");

$sitequestions = array(
    array(
        'question'=>'Is Archive Binge stealing/rehosting comics?',
        'answer'=>'<p><strong>No. Big NO. Absolutely not.</strong> And we have no intention of doing such. <em>Ever</em>. All comics listings on this site point to one or more of the actual mirrors for the comic. No comics are hosted or rehosted or mirrored here. The Reader shows the primary mirror of the comic in an iframe, which ensures that the actual comic site gets the traffic and pageviews. The comic owner may see Reader traffic as a referral from Archive Binge in their analytics trackers.</p>'),
    array(
        'question'=>'How do I add a comic?',
        'answer'=>'<p>Comic submission is restricted to registered users. You can register for an account <a href="/login">here</a>. After that, you\'ll see "Add Comic" under the Site<span class="caret"></span> menu.</p>'),
    array(
        'question'=>'I can\'t edit a comic!',
        'answer'=>'<p>To prevent abuse, once a comic owner has claimed their comic, only the comic owners or the site administrator can edit a comic listing. If you own a comic listed here, you can claim it by following the instructions under the "Claim" button on the details page. After the comic has been claimed, the "Edit" button will appear on the comic details page and you may edit the listing.</p>
<p>If a comic has not been claimed, anyone with an account may contribute to the comic\'s listing.</p>'),
    array(
        'question'=>'Someone added a mirror of my comic as a listing so now there are two listings for the same comic',
        'answer'=>'<p>There are pros and cons to having separate listings for each mirror: On one hand, you may want the separate entries so the reader works on any of your comic\'s mirrors. However, this does dilute your readers and subscribers a bit, so if site ranking is something you care about I would recommend against splitting the entries. If you would like the second listing removed, please claim both listings (or one of them if it\'s obvious they\'re the same comic) and email me which listing you would like removed at <a href="mailto:'.$SUPPORT_EMAIL.'">'.$SUPPORT_EMAIL.'</a>.</p>'),
    array(
        'question'=>'It\'s not finding pages for my comic!',
        'answer'=>'<p>Archive Binge uses a few different methods to find comic pages:</p>
<ul>
    <li><strong>Page Crawl:</strong> Based on the connection between the first page and the second page, guess how to get to the third page and so on.</li>
    <li><strong>Archive Binge:</strong> Scrape the comic\'s archive page for the list of pages. Best for comics with large archives or those that can\'t work with the Page Crawl method.</li>
    <li><strong>Tapas:</strong> Tapas comics only. Pull the list of pages from the page listing on any Tapas episode.
    <li><strong>Webtoons:</strong> LINE Webtoons comics only. Pull the list of pages from the episode list page(s).</li>
</ul>
<p>If none of the above work, I can manually add pages for a comic and work on adjusting the crawler for that comic. To avoid that situation, follow these recommendations:</p>
<ul>
    <li>Use a unique class, rel, id, or title on the link leading to the next page on your comic:
            <pre>&lt;a rel="next" href="nextpage.php"&gt;Next&lt;/a&gt;</pre>
    </li>
    <li>Have an archive page listing the links to all pages of your comic (preferably not buried inside chapter pages).
        <ul>
            <li><a href="http://www.questionablecontent.net/archive.php" target="_blank"><em>Questionable Content</em> Archive</a> as an example of a comic that would work best with the \'Archive Binge\' crawl method.</li>
            <li>Example of an archive page that would not work for the Archive Binge crawl method (but works great with Page Crawl!): <a href="http://spidersilkcomic.com/comic/archive" target="_blank"><em>Spidersilk</em> Archive.</a></li>
        </ul>
    </li>
</ul>
<p>Once you\'ve implemented one of these methods, try initiating a recrawl. If it still doesn\'t catch all of the pages, email me the comic url at <a href="mailto:'.$SUPPORT_EMAIL.'">'.$SUPPORT_EMAIL.'</a> and I\'ll see what I can do.</p>'),
    array(
        'question'=>'Some older pages are missing from my comic\'s listing/reader!',
        'answer'=>'<p>Uhhhhh......Unless you just recently added those pages to your comic (in which case, see next question), I honestly have no idea how that would happen. Please email me your comic at <a href="mailto:'.$SUPPORT_EMAIL.'">'.$SUPPORT_EMAIL.'</a>. For science. Comic owners can also manually edit the list of pages for their claimed comics on the Edit screen.</p>'),
    array(
        'question'=>'I removed/reordered some pages from my comic and now the page listing/reader is wrong!',
        'answer'=>'<p>It takes AB a little bit to figure out you\'ve changed pages. AB currently prioritizes crawling newly-added comics first, then manually-requested crawls, then comics that have gone the longest without a crawl. If you want your page crawled sooner, put in a crawl request (on the info page) and it will be recrawled soon-ish. If you REALLY need it updated sooner or the crawler isn\'t working for whatever reason, you can manually edit the page list on the Crawler Configuration page.</p>'),
    array(
        'question'=>'My primary mirror is at Tapas/Webtoon or another place I can\'t edit the page HTML or DNS to claim the comic.',
        'answer'=>'<p>On your profile page or the Claim page for the comic, you\'ll find a unique Claim token. Drop that token in the author comments or tags of the comic (the comic site, not the AB listing) and <a href="mailto:'.$SUPPORT_EMAIL.'">email me</a> the comic page/episode with the token-comment. If the token is valid, I\'ll set you up as the owner of the comic so you can edit the details.</p>')
);

$tagquestions = array(
    array(
        'question'=>'What tags/warnings are available?',
        'answer'=>'<p>You can find a full list of the tags and warnings available to mark your comics with here:</p>
<ul>
<li><a href="/tags">Tags</a></li>
<li><a href="/warnings">Warnings</a></li>
</ul>'),
    array(
        'question'=>'You\'re missing a tag/warning for [x]:',
        'answer'=>'<p>Probably! If there’s a tag you\'d like to make use of and there\'s nothing close enough in the existing tags, please shoot me an email at <a href="mailto:'.$FEEDBACK_EMAIL.'">'.$FEEDBACK_EMAIL.'</a> and I\'ll consider adding it. I won\'t add tags that are too similar to an existing one, any tags that are offensive (instead of <em>describing</em> offensiveness), tags so generic that literally every comic would fall under it (e.g. a “webcomic” tag) or so specific that they could only ever fit one comic. This means that there won\'t be a tag for “contains stubborn amnesiac jerk that gets himself drowned because of trust issues”.</p>'),
    array(
        'question'=>'You should rename tag/warning [x] to [y].',
        'answer'=>'<p>Email me at <a href="mailto:'.$FEEDBACK_EMAIL.'">'.$FEEDBACK_EMAIL.'</a>. If the issue isn\'t obvious (i.e. I typo\'d a tag/warning), it may help to provide a reason for the change\'s necessity.</p>'),
    array(
        'question'=>'I can\'t use [x] tag on my comic!',
        'answer'=>'<p>Some tags are special and have to be placed on a webcomic by an administrator. Mostly these are tags for collectives or publishers (e.g. Hiveworks, Spider Forest). If your comic belongs to one of these groups or there\'s a special tag applicable to your comic, email me at <a href="mailto:'.$SUPPORT_EMAIL.'">'.$SUPPORT_EMAIL.'</a> with the comic page listing and the tag and I can edit it in.</p>'),
    array(
        'question'=>'A comic is missing content warnings!',
        'answer'=>'<p>If the comic is unclaimed, you can add content warnings! If the comic is claimed, unfortunately the comic listing requires the webcomic owner to add the content warnings. If one is missing a content warning and the comic is egregiously in need of one, email me at <a href="mailto:'.$SUPPORT_EMAIL.'">'.$SUPPORT_EMAIL.'</a> with the comic, the tag that it needs, and an example from the comic (page url, etc) of the content that necessitates the tag.</p>
<p>Please note that I won\'t honor every single request like this. If there is question regarding the validity of the tag\'s necessity, the request may be denied or deferred to the comic\'s owner for a final decision. However, I accept that my judgement may be wrong&mdash;if I receive multiple requests for a tag to be added to a comic, the issue will be reopened and re-reviewed.</p>
<p><strong>If an admin adds a tag to a comic because the owner neglected or refused to, the content tag will not be removed without appeal.</strong></p>')
);

$readingquestions = array(
    array(
        'question'=>'Subscribe &rarr; Private?',
        'answer'=>'<p>Ever have a webcomic that you like to read, but don\'t want anyone else know you\'re reading it? Yeah. You know the one.</p>
<p>Here you can privately subscribe to a webcomic to keep track of your place in the comic, but we\'ll never share that you\'re subscribed to it with anyone. You will not appear on the list of readers (although the comic creator will see a higher number of readers than are actually listed, so they can see the <em>actual</em> number of subscribers&mdash;number, not names), the comic won\'t appear in your friends\' "Friends\' Reading Lists" or recommendations, but the comic (and your bookmark) will appear in your own private reading list.</p>'),
    array(
        'question'=>'Why can\'t I read some comics in the Reader?',
        'answer'=>'<p>Unfortunately some websites, most predominantly Webtoon and several Hiveworks comics, make use of a certain response header that prevents other websites from embedding their content in a frame. This is not necessarily a bad thing and is a valid (if somewhat unnecessary in this case) security practice. The downside is it prevents these comics from appearing in the Reader.</p>
        <p>While <em>technically</em> there is a workaround for this, any workarounds would prevent the traffic from going to the actual webcomic site, which is the opposite of Archive Binge\'s mission. As a compromise, subscribers have the option to manually mark their place in the affected comics. Webcomic owners, if you would like your webcomic to be functional in the Reader, please see the information in this blog post regarding the Reader: <a href="https://archivebinge.com/blog/archive-binge-compatibility/">Archive Binge Compatibility.</a>')
);

$metaquestions = array(
    array(
        'question'=>'Can Archive Binge host my comic?',
        'answer'=>'<p>No, Archive Binge is not a comic host. We don\'t host any of the comics listed here. For hosting a comic, my personal recommendations are (in more or less the order of how highly I recommend them):</p>
<ul>
<li><a href="https://comicfury.com/" target="_blank">Comic Fury</a></li> - CF allows <a href="https://comicfury.com/moreinfo.php" target="_blank">custom domain names and customized layouts</a>.</li>
<li>Self-hosting: <a href="https://freejeeves.com/#creators-cms" target="_blank">Grawlix</a></li>
<li>Self-hosting: <a href="https://wordpress.org/" target="_blank">Wordpress</a> + <a href="http://frumph.net/" target="_blank">ComicPress</a></li>
<li><a href="https://tapas.io/" target="_blank">Tapas</a></li>
<li><a href="https://www.tumblr.com" target="_blank">Tumblr</a></li> - Tumblr also allows custom domains and layouts, but it may erroneously flag pages as adult content.
<li><a href="http://www.webtoons.com/en/" target=_blank">Webtoons</a></li>
</ul>
<p>If you want to go the self-hosting route, you can find some extra info here: <a href="https://nattosoup.blogspot.com/2017/06/self-hosting-your-webcomic-alternatives.html" target="_blank">Self-Hosting Your Webcomic: Alternatives to Tapas, Webtoons</a> or here: <a href="http://freejeeves.com/#creators" target="_blank">Free Jeeves</a></p>'),
    array(
        'question'=>'Any other issues/questions',
        'answer'=>'<p>Email me at <a href="mailto:'.$SUPPORT_EMAIL.'">'.$SUPPORT_EMAIL.'</a> for issues with comic listings, your account, site problems, etc. Email <a href="mailto:'.$FEEDBACK_EMAIL.'">'.$FEEDBACK_EMAIL.'</a> if you have feedback or suggestions. You can also reach me on Twitter <a href="https://twitter.com/'.$TW_HANDLE.'">@'.$TW_HANDLE.'</a>.</p>')
);
?>
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>


    <div class="container panel panel-default">
    <div class="panel-body">

<div class="row">

<div class="col-md-4 col-md-push-8">

    <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">Table of Contents</div>
            <div class="panel-body">

              <div id="table-of-contents">
                  <ul class='list-unstyled'>
                  <strong>Adding Comics</strong>
                  <?php $index = 0; foreach ($sitequestions as $question){echo '<li style=text-indent:-2em;margin-left:2em;><a href="#question-'.$index.'">'.$question['question'].'</a></li>';$index++;} ?>
                  <strong>Tags and Warnings</strong>
                  <?php foreach ($tagquestions as $question){echo '<li style=text-indent:-2em;margin-left:2em;><a href="#question-'.$index.'">'.$question['question'].'</a></li>';$index++;} ?>
                  <strong>Reading Comics</strong>
                  <?php foreach ($readingquestions as $question){echo '<li style=text-indent:-2em;margin-left:2em;><a href="#question-'.$index.'">'.$question['question'].'</a></li>';$index++;} ?>
                  <strong>Archive Binge Questions</strong>
                  <?php foreach ($metaquestions as $question){echo '<li style=text-indent:-2em;margin-left:2em;><a href="#question-'.$index.'">'.$question['question'].'</a></li>';$index++;} ?>
                  </ul>
              </div>

            </div>
        </div><!--panel-->
    </div>
    </div>
</div><!--col-->



<div class="col-md-8 col-md-pull-4 text-justify">


<h1>Site FAQ</h1>

<div class="alert alert-warning">
  <strong>Warning!</strong> This FAQ was written in the future where all site features have already been implemented. Some features described on this page may be buggy or missing in the present.
</div>

<h2>Adding Comics</h2>

<?php $index = 0; foreach ($sitequestions as $question){echo '<h3 id="question-'.$index.'">'.$question['question'].'</h3>
'.$question['answer'];$index++;} ?>

<h2>Tags and Warnings</h2>
<?php foreach ($tagquestions as $question){echo '<h3 id="question-'.$index.'">'.$question['question'].'</h3>
'.$question['answer'];$index++;} ?>

<h2>Reading Comics</h2>
<?php foreach ($readingquestions as $question){echo '<h3 id="question-'.$index.'">'.$question['question'].'</h3>
'.$question['answer'];$index++;} ?>

<h2>Archive Binge Questions</h2>
<?php foreach ($metaquestions as $question){echo '<h3 id="question-'.$index.'">'.$question['question'].'</h3>
'.$question['answer'];$index++;} ?>

<hr />
<p><a href="#top">Back to the top</a></p>

</div>

</div><!--row-->
</div><!--panel body-->


    </div> <!-- /container -->

	<?php require_once 'includes/footer.inc.php';?>
  </body>
</html>
