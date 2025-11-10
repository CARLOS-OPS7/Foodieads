<?php
$isEmbed = isset($_GET['embed']);
?>
<?php if (!$isEmbed): ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AI Console</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="https://unpkg.com/htmx.org@1.9.12"></script>
</head>
<body class="bg-slate-50" data-bot-mode="server">
  <div class="max-w-3xl mx-auto px-6 py-8">
<?php endif; ?>

  <div class="fa-card p-6" id="ai-console">
    <h2 class="text-lg font-semibold mb-3">AI Console (beta)</h2>
    <p class="text-slate-600 text-sm mb-4">Type admin intents like "create ad Pizza Promo", "approve user #12", "upload hero image", "enable feature X". This will route to safe server-side handlers.</p>

    <form hx-post="portal_ai_console_action.php" hx-target="#ai-result" hx-swap="innerHTML" class="flex gap-2">
      <input name="command" class="flex-1 border border-slate-200 rounded px-3 py-2" placeholder="e.g. create ad 'Weekend Deal' budget 1000">
      <button class="px-3 py-2 rounded bg-indigo-600 text-white">Run</button>
    </form>
    <div id="ai-result" class="mt-4 text-sm"></div>
  </div>

<?php if (!$isEmbed): ?>
  </div>
</body>
</html>
<?php endif; ?>



