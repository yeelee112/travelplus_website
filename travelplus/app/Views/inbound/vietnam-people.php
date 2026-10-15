<?php
$peopleMoments = [
    ['shared-table', 'A place at the table', 'Sharing dishes. Passing bowls. Making time for one another.', 'AI illustration of Vietnamese adults sharing a meal at home'],
    ['morning-pho', 'Good mornings start here', 'A steaming bowl, a familiar face and the rhythm of a neighbourhood.', 'AI illustration of a Vietnamese pho vendor serving adult customers'],
    ['market-conversation', 'More than a morning market', 'Fresh herbs, everyday conversations and small moments of connection.', 'AI illustration of a produce seller and shopper talking at a Vietnamese market'],
    ['pottery-hands', 'Stories shaped by hand', 'Slow down and discover the patience behind a handmade bowl.', 'AI illustration of a Vietnamese artisan guiding an adult visitor at a pottery wheel'],
];
?>
<section class="container inbound-people" aria-labelledby="inbound-people-title">
    <div class="inbound-section-head">
        <div><span class="inbound-section-kicker">Beyond the scenery</span><h2 id="inbound-people-title">Meet Vietnam,<br><em>through its people.</em></h2></div>
        <p>The meals we share. The conversations we remember. Get a little closer to the everyday life that gives a journey its meaning.</p>
    </div>
    <div class="inbound-people__grid">
        <?php foreach ($peopleMoments as [$asset, $title, $caption, $alt]): ?>
            <figure class="inbound-people__moment">
                <img src="<?= esc(base_url('assets/images/landing/inbound/people/' . $asset . '.webp'), 'attr') ?>" alt="<?= esc($alt, 'attr') ?>" width="1536" height="1024" loading="lazy" decoding="async">
                <figcaption><h3><?= esc($title) ?></h3><p><?= esc($caption) ?></p></figcaption>
            </figure>
        <?php endforeach; ?>
    </div>
    <p class="inbound-people__disclosure">AI-generated illustrations inspired by everyday life in Vietnam. These are not photographs of Travel Plus guests or confirmed tour activities.</p>
    <a class="inbound-story-link" href="#plan-my-trip">Bring local experiences into my journey <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
</section>
