const fs=require('fs');const p='app/Views/autumn/index.php';let s=fs.readFileSync(p,'utf8');s=s.replace('<h3><?= esc($place[$en ? 1 : 0]) ?></h3></div>\n     <ul>', '</div>\n     <div class="at-region-overlay"><h3><?= esc($place[$en ? 1 : 0]) ?></h3><ul>');s=s.replace('     <?php endforeach ?></ul>','     <?php endforeach ?></ul></div>');fs.writeFileSync(p,s);
const css='public/assets/css/style-autumn.css';s=fs.readFileSync(css,'utf8');const start=s.indexOf('/* Autumn routes supplied by Travel Plus');s=s.slice(0,start)+`/* Photo-led autumn destinations with the supplied Travel Plus itineraries. */
.at-collection-regions{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:18px}
.at-region-card{position:relative;isolation:isolate;min-width:0;min-height:390px;border-radius:14px;overflow:hidden;background:#344630;transition:transform .25s}
.at-region-card:hover{transform:translateY(-4px)}
.at-region-photo{position:absolute;inset:0;z-index:-2}
.at-region-photo img{width:100%;height:100%;object-fit:cover;transition:transform .4s}
.at-region-card:hover .at-region-photo img{transform:scale(1.04)}
.at-region-card:after{content:'';position:absolute;inset:0;z-index:-1;background:linear-gradient(0deg,rgba(16,33,23,.97),rgba(16,33,23,.72) 38%,transparent 78%)}
.at-region-overlay{padding:20px 17px;position:absolute;bottom:0;left:0;right:0}
.at-region-overlay h3{color:#fff;font-size:25px;font-weight:700;margin-bottom:10px}
.at-region-card ul{list-style:none;margin:0;padding:0}
.at-region-card li a{display:flex;align-items:center;justify-content:space-between;gap:8px;min-height:35px;padding:6px 0;color:#fff5e1;font-size:13px;font-weight:500;line-height:1.5}
.at-region-card li a i{flex-shrink:0;font-size:12px;color:#f5ce8a}
.at-region-card li a:hover{color:#ffce7b;text-decoration:underline;text-underline-offset:4px}
@media(min-width:768px) and (max-width:1100px){.at-collection-regions{grid-template-columns:repeat(3,minmax(0,1fr))}.at-region-card{min-height:370px}}
@media(max-width:767px){.at-collection-regions{display:flex;gap:14px;overflow-x:auto;scroll-snap-type:x mandatory;padding-bottom:12px}.at-region-card{flex:0 0 80%;min-height:380px;scroll-snap-align:start}.at-region-overlay{padding:22px}.at-region-overlay h3{font-size:26px}.at-region-card li a{min-height:40px;font-size:14px}}
@media(prefers-reduced-motion:reduce){.at-region-card,.at-region-photo img{transition:none}}
`;fs.writeFileSync(css,s);
