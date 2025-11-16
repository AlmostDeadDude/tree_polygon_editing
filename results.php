<?php
$proofCode = 'demoProofCode';

@require_once('header.php');

echo '<section class="info-block results-callout">
<h1>Thanks for trying the demo!</h1>
<p>This interface now runs in a restricted, read-only mode: edits stay in your browser and the backend simply issues a mock proof code so the original crowdsourcing platform flow is easy to showcase.</p>
<div class="demo-badge">Shareable proof code: <strong id="proofCodeContainer">' . htmlspecialchars($proofCode) . '</strong></div>
<button id="copyProofCodeBtn">Copy proof code</button>
<p class="muted">Need something to explore next? Open the action log viewer to replay genuine worker submissions gathered during the real campaigns.</p>
<div class="demo-actions">
  <a class="action primary" target="_blank" rel="noopener noreferrer" href="userLogsView.php">Browse sample logs</a>
  <a class="action secondary" href="about.php">Learn more about the research</a>
</div>
</section>';

echo '<section class="info-block">
<h2>What happens to my edits?</h2>
<p>Originally, every click was stored in <code>results/</code>, <code>user_info/</code>, and <code>user_logs/</code>. For portfolio deployments, the editor keeps running but the save endpoint simply responds with a demo status. Nothing new is written to disk, while the historic datasets remain available for replay.</p>
</section>';

echo '<section class="info-block">
<h2>Session recap</h2>
<p>The quick stats below are hydrated on the client-side. They only exist in <code>sessionStorage</code> so you can refresh for a clean slate.</p>
<div class="info-grid" id="demoSummary">
  <article class="info-card">
    <h3>Polygons edited</h3>
    <p id="summaryPolygons">–</p>
  </article>
  <article class="info-card">
    <h3>Job identifier</h3>
    <p id="summaryJob">–</p>
  </article>
  <article class="info-card">
    <h3>Run time</h3>
    <p id="summaryTime">–</p>
  </article>
</div>
</section>';

echo '<script>
document.addEventListener("DOMContentLoaded", () => {
  const recapData = sessionStorage.getItem("polygonDemoSubmission");
  if (!recapData) {
    return;
  }
  try {
    const parsed = JSON.parse(recapData);
    if (parsed.polygonsEdited !== undefined) {
      document.getElementById("summaryPolygons").innerText = parsed.polygonsEdited;
    }
    if (parsed.dataInfo && parsed.dataInfo.job) {
      document.getElementById("summaryJob").innerText = "Job " + parsed.dataInfo.job;
    }
    if (parsed.timestamp) {
      const date = new Date(parsed.timestamp);
      document.getElementById("summaryTime").innerText = date.toLocaleString();
    }
  } catch (error) {
    console.error("Unable to parse demo recap", error);
  }
  sessionStorage.removeItem("polygonDemoSubmission");
});
</script>';

@require_once('footer.php');
