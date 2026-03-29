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
                aria-label="Image précédente"
                style="position:absolute;left:12px;top:50%;transform:translateY(-50%);z-index:10;background:rgba(0,0,0,0.45);border:none;border-radius:9999px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s"
                onmouseover="this.style.background='rgba(0,0,0,0.7)'"
                onmouseout="this.style.background='rgba(0,0,0,0.45)'">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>
        <!-- Next -->
        <button type="button" onclick="carouselMove('<?= $html($cid) ?>', 1)"
                aria-label="Image suivante"
                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);z-index:10;background:rgba(0,0,0,0.45);border:none;border-radius:9999px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s"
                onmouseover="this.style.background='rgba(0,0,0,0.7)'"
                onmouseout="this.style.background='rgba(0,0,0,0.45)'">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"/>
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
