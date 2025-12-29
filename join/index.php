<?php
require_once __DIR__ . '/../config/config.php';

$db = getCampaignDB();
$today = date('Y-m-d');

$stmt = $db->prepare("
    SELECT c.*, ca.file_path
    FROM campaigns c
    LEFT JOIN campaign_assets ca ON ca.campaign_id = c.id
    WHERE c.status='active'
      AND c.start_date <= ?
      AND (c.end_date IS NULL OR c.end_date >= ?)
    LIMIT 1
");
$stmt->execute([$today, $today]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$campaign) die("No active campaign");

$q = $db->prepare("SELECT * FROM campaign_questions WHERE campaign_id=? ORDER BY sort_order");
$q->execute([$campaign['id']]);
$questions = $q->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($campaign['title']) ?> | LIYAS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">

<style>
:root{
    --blue1:#1fb6ff;
    --blue2:#009eea;
    --cyan:#39c6e6;
    --navy:#1a1f36;
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Outfit',sans-serif;background:#fff;overflow-x:hidden}
.container{display:flex;min-height:100vh}

/* LEFT */
.left{
    flex:1;padding:80px;
    background:linear-gradient(135deg,var(--blue1),var(--blue2));
    color:#fff;position:relative;overflow:hidden
}
.logo{position:absolute;top:30px;left:30px;width:42px;filter:brightness(0)}
.left-header{text-align:center;margin-top:20px}
.left-header h1{font-size:5.5rem;font-weight:900;margin-bottom:20px}
.left-header p{max-width:420px;margin:0 auto;opacity:.9}
.product{
    margin-top:50px;width:220px;background:#f1f5f9;
    padding:20px;border-radius:6px;
    box-shadow:0 20px 40px rgba(0,0,0,.2)
}
.product img{width:100%}


/* WAVES */
.waves{position:absolute;bottom:0;left:0;width:100%;height:200px}
.parallax>use{animation:move-forever 25s cubic-bezier(.55,.5,.45,.5) infinite}
.parallax>use:nth-child(1){animation-delay:-2s;animation-duration:7s}
.parallax>use:nth-child(2){animation-delay:-3s;animation-duration:10s}
.parallax>use:nth-child(3){animation-delay:-4s;animation-duration:13s}
.parallax>use:nth-child(4){animation-delay:-5s;animation-duration:20s}
@keyframes move-forever{
    0%{transform:translate3d(-90px,0,0)}
    100%{transform:translate3d(85px,0,0)}
}

/* RIGHT */
.right{
    flex:1;padding:80px;
    display:flex;align-items:center;justify-content:center;
    background:
        radial-gradient(circle at 100% 0%, rgba(57,198,230,.25), transparent 55%),
        radial-gradient(circle at 0% 100%, rgba(31,182,255,.18), transparent 60%),
        linear-gradient(180deg,#f9fdff 0%,#fff 100%);
}
.form{max-width:500px;width:100%}
.step{font-size:.75rem;letter-spacing:3px;color:var(--cyan);font-weight:700;margin-bottom:12px}
.form h2{font-size:3.2rem;color:var(--navy);margin-bottom:45px}
.form h2 span{color:var(--cyan)}
.input{margin-bottom:50px}
.input input{
    width:100%;border:none;border-bottom:2px solid #e2e8f0;
    padding:14px 0;font-size:1.1rem;background:none
}
.actions{display:flex;gap:20px}
.btn{
    padding:18px 50px;background:var(--navy);
    color:#fff;border:none;border-radius:14px;
    font-weight:700;cursor:pointer
}
.back{background:none;border:none;color:#94a3b8;font-weight:600;cursor:pointer}
.step-card{display:none}
.step-card.active{display:block;animation:fade .6s ease}
@keyframes fade{from{opacity:0;transform:translateY(10px)}to{opacity:1}}

@media(max-width:1024px){
    .container{flex-direction:column}
    .left{padding:40px}
    .right{padding:50px 30px}
    .left-header h1{font-size:3.5rem}
}

/* ===== SUCCESS POPUP ===== */
#successOverlay{
    position:fixed;inset:0;
    background:rgba(15,23,42,.85);
    display:none;align-items:center;justify-content:center;
    z-index:9999;
}
.success-box{
    position:relative;
    background:#fff;
    padding:70px 60px;
    border-radius:36px;
    width:420px;max-width:90%;
    text-align:center;
    overflow:hidden;
    animation:boxIn .6s ease;
}
.check-circle{
    width:120px;height:120px;
    border-radius:50%;
    background:#22c55e;
    color:#fff;font-size:56px;
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 25px;
    animation:checkPulse 1.2s ease infinite alternate;
}
.success-box h1{
    font-size:2.2rem;
    font-weight:900;
    color:#0f172a;
    margin-bottom:6px;
}
.success-box .thanks{
    font-size:1.05rem;
    color:#475569;
    margin-bottom:6px;
}
.success-box .redirect{
    font-size:.95rem;
    color:#94a3b8;
}

/* CONFETTI */
.confetti{
    position:absolute;inset:0;pointer-events:none;
}
.c{
    position:absolute;
    width:18px;height:18px;
    border-radius:6px;
    top:50%;left:50%;
    opacity:0;
    background:var(--clr);
}
.confetti.play .c{
    animation:confettiBurst 2.6s ease-out forwards;
}

@keyframes confettiBurst{
    0%{
        transform:translate(-50%,-50%) scale(.3) rotate(0deg);
        opacity:1;
    }
    100%{
        transform:
          translate(
            calc(-50% + (var(--x)*360px - 180px)),
            calc(-50% + (var(--y)*260px - 130px))
          )
          scale(1)
          rotate(720deg);
        opacity:0;
    }
}
@keyframes boxIn{from{opacity:0;transform:scale(.85)}to{opacity:1;transform:scale(1)}}
@keyframes checkPulse{from{transform:scale(1)}to{transform:scale(1.08)}}
</style>
</head>
<body>

<div class="container">

<!-- LEFT -->
<div class="left">
    <img src="../assets/images/logo/logo.png" class="logo">
    <div class="left-header">
        <h1><?= htmlspecialchars($campaign['title']) ?></h1>
        <p><?= htmlspecialchars($campaign['description'] ?? '') ?></p>
    </div>
    <?php if($campaign['file_path']): ?>
    <div class="product">
        <img src="../<?= htmlspecialchars($campaign['file_path']) ?>">
    </div>
    <?php endif; ?>

    <svg class="waves" viewBox="0 24 150 28" preserveAspectRatio="none">
        <defs><path id="gentle-wave"
        d="M-160 44c30 0 58-18 88-18s58 18 88 18 58-18 88-18 58 18 88 18v44h-352z"/></defs>
        <g class="parallax">
            <use href="#gentle-wave" x="48" y="0" fill="rgba(255,255,255,.4)" />
            <use href="#gentle-wave" x="48" y="3" fill="rgba(255,255,255,.3)" />
            <use href="#gentle-wave" x="48" y="5" fill="rgba(255,255,255,.2)" />
            <use href="#gentle-wave" x="48" y="7" fill="rgba(255,255,255,.15)" />
        </g>
    </svg>
</div>

<!-- RIGHT -->
<div class="right">
<div class="form">
<form method="POST" action="submit.php" enctype="multipart/form-data">
<input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">

<div class="step-card active">
    <div class="step">STEP 01</div>
    <h2>May we have your<br><span>full name?</span></h2>
    <div class="input"><input type="text" name="full_name" required></div>
</div>

<div class="step-card">
    <div class="step">STEP 02</div>
    <h2>How can we<br><span>contact you?</span></h2>
    <div class="input">
        <input type="email" name="email" required>
        <input type="tel" name="phone_number" required>
    </div>
</div>

<?php foreach($questions as $i=>$q): ?>
<div class="step-card">
    <div class="step">STEP <?= str_pad($i+3,2,'0',STR_PAD_LEFT) ?></div>
    <h2><?= htmlspecialchars($q['question_label']) ?></h2>
    <div class="input">
        <?php if($q['field_type']==='file'): ?>
            <input type="file" name="media[<?= $q['id'] ?>]">
        <?php else: ?>
            <input type="<?= $q['field_type'] ?>" name="answers[<?= $q['id'] ?>]">
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<div class="actions">
    <button type="button" class="btn" id="nextBtn" onclick="move(1)">Continue</button>
    <button type="submit" class="btn" id="submitBtn" style="display:none">Submit</button>
    <button type="button" class="back" id="prevBtn" onclick="move(-1)" style="visibility:hidden">Back</button>
</div>
</form>
</div>
</div>
</div>

<!-- SUCCESS OVERLAY -->
<div id="successOverlay">
  <div class="success-box">
    <div class="confetti">
      <?php for($i=0;$i<28;$i++): ?><span class="c"></span><?php endfor; ?>
    </div>
    <div class="check-circle">✓</div>
    <h1>Submitted Successfully!</h1>
    <p class="thanks">Thank you for participating.</p>
    <p class="redirect">Redirecting…</p>
  </div>
</div>

<script>
let step=0;
const cards=document.querySelectorAll('.step-card');
const nextBtn=document.getElementById('nextBtn');
const submitBtn=document.getElementById('submitBtn');
const prevBtn=document.getElementById('prevBtn');
const overlay=document.getElementById('successOverlay');
const form=document.querySelector('form');

function update(){
    cards.forEach((c,i)=>c.classList.toggle('active',i===step));
    prevBtn.style.visibility=step===0?'hidden':'visible';
    nextBtn.style.display=step===cards.length-1?'none':'inline-block';
    submitBtn.style.display=step===cards.length-1?'inline-block':'none';
}
function move(n){
    if(n===1){
        const inputs=cards[step].querySelectorAll('[required]');
        for(const i of inputs){ if(!i.checkValidity()){ i.reportValidity(); return; } }
    }
    step=Math.max(0,Math.min(step+n,cards.length-1));
    update();
}

form.addEventListener('submit',e=>{
    e.preventDefault();
    submitBtn.disabled=true;
    overlay.style.display='flex';

    const confetti=document.querySelector('.confetti');
    const colors=['#1fb6ff','#22c55e','#facc15','#fb7185','#60a5fa','#34d399','#fbbf24','#818cf8'];

    confetti.classList.remove('play');
    void confetti.offsetWidth;

    confetti.querySelectorAll('.c').forEach(el=>{
        el.style.setProperty('--x',Math.random());
        el.style.setProperty('--y',Math.random());
        el.style.setProperty('--clr',colors[Math.floor(Math.random()*colors.length)]);
    });

    confetti.classList.add('play');
    setTimeout(()=>form.submit(),3000);
});

update();
</script>

</body>
</html>
