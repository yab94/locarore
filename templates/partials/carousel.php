<?php
/**
 * Partial carousel générique.
 *
 * Paramètres attendus (via $data ou injectés dans $tpl) :
 *   - photos  : array{ photo: object, label: string }[]  — liste des slides
 *   - id      : string  — identifiant HTML unique du carousel
 *
 * Chaque entrée de photos doit avoir :
 *   - photo->getPublicPath() : string
 *   - photo->getDescription(): ?string
 *   - label                  : string  — fallback pour alt/title
 */
use RRB\View\HtmlEncoder;
use RRB\Type\Cast;

$html   = HtmlEncoder::cast($tpl->get('html'));
$photos = Cast::array($tpl->get('carouselPhotos'));
$cid    = Cast::string($tpl->get('carouselId'));
$total  = count($photos);
?>
<?php if ($total > 0): ?>
<div class="relative rounded-2xl overflow-hidden bg-gray-100 group" id="<?= $html($cid) ?>">

    <!-- Track -->
    <div class="carousel-track flex transition-transform duration-300 ease-in-out h-96"
         style="width:<?= $total * 100 ?>%">
        <?php foreach ($photos as $i => $entry): ?>
            <?php $alt = $html($entry['photo']->getDescription() ?: $entry['label']); ?>
            <div style="width:<?= round(100 / $total, 4) ?>%" class="shrink-0 h-96">
                <img src="<?= $html($entry['photo']->getPublicPath()) ?>"
                     alt="<?= $alt ?>"
                     title="<?= $alt ?>"
                     width="768" height="384"
                     <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>
                     class="w-full h-full object-cover">
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($total > 1): ?>
        <!-- Prev -->
        <button type="button" onclick="carouselMove('<?= $html($cid) ?>', -1)"
                class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full p-2 shadow transition opacity-0 group-hover:opacity-100">
            <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <!-- Next -->
        <button type="button" onclick="carouselMove('<?= $html($cid) ?>', 1)"
                class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full p-2 shadow transition opacity-0 group-hover:opacity-100">
            <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        <!-- Dots -->
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
            <?php for ($i = 0; $i < $total; $i++): ?>
                <button type="button"
                        onclick="carouselGo('<?= $html($cid) ?>', <?= $i ?>)"
                        class="carousel-dot w-2 h-2 rounded-full transition <?= $i === 0 ? 'bg-white' : 'bg-white/50 hover:bg-white/80' ?>">
                </button>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="rounded-2xl bg-gray-100 h-96 flex items-center justify-center text-gray-400">
    Pas de photo
</div>
<?php endif; ?>
