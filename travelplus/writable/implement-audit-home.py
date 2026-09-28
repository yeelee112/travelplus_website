from pathlib import Path
p=Path('app/Views/sections/home-tour.php');s=p.read_text(encoding='utf-8');start=s.index('        <div class="home-summer-spotlight">');end=s.index('        <div class="home-tour-grid">',start);s=s[:start]+'''        <div class="home-section-head">
            <div><h2 id="home-tour-title"><?= esc($copy['listTitle']) ?></h2><p><?= esc($copy['desc']) ?></p></div>
            <a class="home-section-link" href="<?= esc($allToursUrl, 'attr') ?>"><?= esc($copy['allToursCta']) ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>

'''+s[end:];p.write_text(s,encoding='utf-8')
p=Path('app/Views/sections/hero-search.php');s=p.read_text(encoding='utf-8');start=s.index('<section class="home-hero-trust-section"');trust=s[start:];s=s[:start];p.write_text(s,encoding='utf-8');# Keep trust copy close to its rendering, with a compact layout.
trustdata=s[s.index('$trustItems ='):s.index('?>',s.index('$trustItems ='))];Path('app/Views/sections/home-trust.php').write_text("<?php\n$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';\n"+trustdata+'?>\n'+trust,encoding='utf-8');s=s.replace(trustdata,'');p.write_text(s,encoding='utf-8')
p=Path('app/Views/home/index.php');s=p.read_text(encoding='utf-8');s=s.replace("    <?= $this->include('sections/home-promotions') ?>\n    <?= $this->include('sections/home-tour') ?>\n    <?= $this->include('sections/custom-tour-cta') ?>\n    <?= $this->include('sections/home-passport') ?>", "    <?= $this->include('sections/home-tour') ?>\n    <?= $this->include('sections/home-promotions') ?>\n    <?= $this->include('sections/home-trust') ?>")
s=s.replace("    <?= $this->include('sections/home-blog') ?>", "    <?= $this->include('sections/home-services-compact') ?>\n    <?= $this->include('sections/home-blog') ?>")
s=s.replace("    <?= $this->include('sections/counter') ?>\n    <?= $this->include('sections/gallery-home') ?>",'''    <details class="home-more-stories container">
        <summary><?= service('request')->getLocale() === 'en' ? 'More about Travel Plus · Photos & milestones' : 'Thêm về Travel Plus · Hình ảnh & dấu ấn' ?><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
        <?= $this->include('sections/counter') ?>
        <?= $this->include('sections/gallery-home') ?>
    </details>''');p.write_text(s,encoding='utf-8')
