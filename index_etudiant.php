<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mon Module et Assistant IA</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-8 px-4">

  <!-- Header -->
  <header class="w-full max-w-5xl mb-10">
    <div class="flex justify-between items-center">
      <div class="flex items-center gap-4">
        <i class="fas fa-user-circle text-4xl text-gray-700"></i>
        <h1 class="text-2xl font-semibold">Bonjour, <?php echo htmlspecialchars($etudiantNom . " " . $etudiantPrenom); ?></h1>
      </div>
      <span class="text-gray-600"><?php echo date('d/m/Y'); ?></span>
    </div>
  </header>

  <!-- Module Section -->
  <section class="w-full max-w-3xl bg-white p-8 rounded-2xl shadow-md mb-10">
    <h2 class="text-2xl font-bold mb-6 text-center">Votre Module</h2>
    <div class="flex flex-col items-center">
      <div class="bg-indigo-500 text-white px-6 py-2 rounded-full mb-4 text-lg font-medium">
        <?php echo $modules[0] ?? 'Aucun module'; ?>
      </div>
      <div class="w-1 h-10 border-l-2 border-dashed border-gray-400"></div>
      <div class="mt-6 text-gray-600 text-center">
        Explorez votre module et commencez votre aventure d’apprentissage !
      </div>
    </div>
  </section>

  <!-- Assistant IA Section -->
  <section class="w-full max-w-4xl bg-white p-8 rounded-2xl shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Ton Assistant IA</h2>

    <div class="flex flex-col space-y-4 mb-6">
      <textarea id="inputText" placeholder="Écrivez ou collez votre texte ici..." 
        class="w-full h-40 p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none"></textarea>

      <button id="summarizeBtn" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-3 rounded-lg transition-all">
        Résumer le texte
      </button>
    </div>

    <div class="hidden" id="resultContainer">
      <h3 class="text-xl font-semibold mb-4">Résumé :</h3>
      <p id="summaryText" class="text-gray-700 bg-gray-100 p-4 rounded-lg"></p>
    </div>
  </section>

  <script>
    document.getElementById('summarizeBtn').addEventListener('click', function() {
      var inputText = document.getElementById('inputText').value;
      var resultContainer = document.getElementById('resultContainer');
      var summaryText = document.getElementById('summaryText');
      
      if (inputText.trim() === "") {
        alert("Veuillez entrer un texte à résumer.");
        return;
      }

      var xhr = new XMLHttpRequest();
      xhr.open("POST", "summarize.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      
      xhr.onload = function() {
        if (xhr.status === 200) {
          var response = JSON.parse(xhr.responseText);
          if (response.summary) {
            summaryText.textContent = response.summary;
            resultContainer.classList.remove('hidden');
          } else {
            summaryText.textContent = "Erreur lors du résumé. Essayez encore.";
            resultContainer.classList.remove('hidden');
          }
        } else {
          alert("Une erreur est survenue. Essayez encore.");
        }
      };

      xhr.send("input_text=" + encodeURIComponent(inputText));
    });
  </script>

</body>
</html>
