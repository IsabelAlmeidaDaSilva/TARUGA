const $startGameButton = document.querySelector(".start-quiz")
const $nextQuestionButton = document.querySelector(".next-question")
const $questionsContainer = document.querySelector(".questions-container")
const $questionText = document.querySelector(".question")
const $answersContainer = document.querySelector(".answers-container")
const $divSQ = document.querySelector(".divStart-quiz")
const $imagemContainerErrado = document.querySelector(".imagemCerta")
const $imagemContainerCerto = document.querySelector(".imagemErrada")
const $imagemCE = document.querySelector(".imagemCE")
const $imagemCongratulations = document.querySelector(".imagemCongratulations")
const tituloGato = document.getElementById("tituloGato");
const progressBar = document.getElementById("progress-container");


let currentQuestionIndex = 0
let totalCorrect = 0

$startGameButton.addEventListener("click", startGame)
$nextQuestionButton.addEventListener("click", displayNextQuestion)

function startGame() {
  $startGameButton.style.display = 'none'
  $divSQ.style.display = 'none'
  $questionsContainer.classList.remove("hide")
  displayNextQuestion()
}

function displayNextQuestion() {
  resetState()

  if (questions.length === currentQuestionIndex) {
    return finishGame()
  }

  $questionText.textContent = questions[currentQuestionIndex].question
  questions[currentQuestionIndex].answers.forEach(answer => {
    const img = document.createElement("img")
    img.src = answer.image
    img.alt = answer.text
    img.classList.add("answer-image")
    if (answer.correct) {
      img.dataset.correct = answer.correct
    }
    $answersContainer.appendChild(img)

    img.addEventListener("click", selectAnswer)
  })
}

function resetState() {
  while ($answersContainer.firstChild) {
    $answersContainer.removeChild($answersContainer.firstChild)
  }

  document.body.removeAttribute("class")
  $nextQuestionButton.classList.add("hide")
  tituloGato.textContent = "Qual será a carta correta?";
  tituloGato.style.color = 'black';
  $imagemCE.classList.remove("esconder")
  $imagemCE.classList.add("mostrar")
  $imagemContainerCerto.classList.add("esconder")
  $imagemContainerErrado.classList.add("esconder")
  $imagemContainerCerto.classList.remove("mostrar")
  $imagemContainerErrado.classList.remove("mostrar")
}

function selectAnswer(event) {
  const answerClicked = event.target

  if (answerClicked.dataset.correct) {
    document.body.classList.add("correct")
    totalCorrect++
    $imagemContainerErrado.classList.remove("esconder")
    $imagemContainerErrado.classList.add("mostrar")
    $imagemCE.classList.add("esconder")
    tituloGato.textContent = "Parabéns você acertou!";
    tituloGato.style.color = '#11ce00';
  } else {
    document.body.classList.add("incorrect")
    $imagemContainerCerto.classList.remove("esconder")
    $imagemContainerCerto.classList.add("mostrar")
    $imagemCE.classList.add("esconder")
    tituloGato.textContent = "Que Pena parece que você errou!";
    tituloGato.style.color = '#c92300';
  }

  document.querySelectorAll(".answer-image").forEach(img => {
    img.classList.add(img.dataset.correct ? "correct" : "incorrect")
    img.style.pointerEvents = "none" // Desativa a interação com as imagens após a seleção
    img.style.opacity = "0.8"; // Desativa a interação com as imagens após a seleção
  })

  $nextQuestionButton.classList.remove("hide")
  currentQuestionIndex++
}

async function finishGame() {
  tituloGato.textContent = " ";
  tituloGato.style.color = 'black';
  $imagemCE.classList.add("esconder");
  $imagemCE.classList.remove("mostrar");
  progressBar.classList.remove("esconder");
  progressBar.classList.add("mostrar");
  $imagemCongratulations.classList.remove("esconder");
  $imagemCongratulations.classList.add("mostrar", "imagem-pulando");

  const totalQuestions = questions.length;
  const performance = Math.floor(totalCorrect * 100 / totalQuestions);
  animarBarraDeProgresso(performance);
  const message = performance >= 90 ? 'Excelente! >-<' : performance >= 70 ? 'Muito bom >wO' : performance >= 50 ? 'Bom ^-^' : 'Continue praticando!';
  const congratulationsUrl = typeof congratulationsRoute !== 'undefined'
    ? congratulationsRoute + window.location.search : '/student/activities/FirstActivity/congratulations';
  $questionsContainer.innerHTML = `
      <div class="final-message">Você acertou ${totalCorrect} de ${totalQuestions} questões!
      <span>Resultado: ${message}</span><p id="save-status" role="status">Salvando sua nota...</p>
      <button type="button" class="button" id="finish-continue" disabled>Continuar</button></div>`;
  const status = document.getElementById('save-status');
  const button = document.getElementById('finish-continue');
  async function registerResult() {
    button.disabled = true;
    button.textContent = 'Salvando...';
    try {
      const response = await fetch(RESULT_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':CSRF_TOKEN },
        credentials: 'same-origin',
        body: JSON.stringify({materia:ACTIVITY_SUBJECT,numeroAtividade:ACTIVITY_NUMBER,correct:totalCorrect,total:totalQuestions,assessment_id:ASSESSMENT_ID || null})
      });
      if ([403, 409].includes(response.status) && ASSESSMENT_ID) {
        status.textContent = 'Esta tentativa já foi encerrada ou registrada. Consulte suas notas.';
        button.disabled = false;
        button.textContent = 'Ver minha nota';
        button.onclick = () => window.location.assign(congratulationsUrl);
        return;
      }
      if (!response.ok) throw new Error('HTTP ' + response.status);
      status.textContent = 'Nota registrada!';
      button.disabled = false;
      button.textContent = 'Continuar e ver resultados';
      button.onclick = () => window.location.assign(congratulationsUrl);
    } catch (error) {
      console.error('Falha ao salvar o resultado',error);
      status.textContent = 'Não foi possível salvar a nota. Confira a conexão e tente novamente.';
      button.disabled = false;
      button.textContent = 'Tentar salvar novamente';
      button.onclick = registerResult;
    }
  }
  await registerResult();
}

// Função para animar a barra de progresso
function animarBarraDeProgresso(pontuacao) {
  const progressBar = document.getElementById("progress-bar"); // Altere para o ID correto
  let currentWidth = 0; // Inicializa a largura atual da barra
  const targetWidth = pontuacao; // A largura desejada da barra
  const increment = 1; // A quantidade a ser incrementada a cada intervalo
  const duration = 1000; // Duração total da animação em milissegundos
  const intervalTime = targetWidth > 0 ? duration / targetWidth : duration; // Tempo entre os incrementos

  // Define a animação da barra
  const interval = setInterval(() => {
    if (currentWidth < targetWidth) {
      currentWidth += increment; // Incrementa a largura atual
      progressBar.style.width = `${currentWidth}%`; // Atualiza a largura da barra
      progressBar.innerText =`${currentWidth}%`; // Atualiza o texto dentro da barra
    } else {
      clearInterval(interval); // Para o intervalo quando a largura desejada é atingida
    }
  }, intervalTime);
}


const questions = [
  {
    question: "Encontre a carta que representa a letra: A",
    answers: [
      { image: IMAGES.aLibras, text: "a", correct: true },
      { image: IMAGES.cLibras, text: "c", correct: false },
      { image: IMAGES.bLibras, text: "b", correct: false },
    ]
  },
  {
    question: "Encontre a carta que representa a letra: G",
    answers: [
      { image: IMAGES.fLibras, text: "f", correct: false },
      { image: IMAGES.gLibras, text: "g", correct: true },
      { image: IMAGES.eLibras, text: "e", correct: false },
    ]
  },
  {
    question: 'Encontre a carta que representa a letra: I',
    answers: [
      { image: IMAGES.hLibras, text: "h", correct: false },
      { image: IMAGES.kLibras, text: "k", correct: false },
      { image: IMAGES.jLibras, text: "j", correct: true },
    ]
  },
  {
    question: 'Encontre a carta que representa a letra: M',
    answers: [
      { image: IMAGES.lLibras, text: "l", correct: false },
      { image: IMAGES.mLibras, text: "m", correct: true },
      { image: IMAGES.nLibras, text: "n", correct: false },
    ]
  },
  {
    question: 'Encontre a carta que representa a letra: O',
    answers: [
      { image: IMAGES.oLibras, text: "o", correct: true },
      { image: IMAGES.qLibras, text: "q", correct: false },
      { image: IMAGES.pLibras, text: "p", correct: false },
    ]
  },
  {
    question: 'Encontre a carta que representa a letra: R',
    answers: [
      { image: IMAGES.tLibras, text: "t", correct: false },
      { image: IMAGES.sLibras, text: "s", correct: false },
      { image: IMAGES.rLibras, text: "r", correct: true },
    ]
  },
  {
    question: 'Encontre a carta que representa a letra: U',
    answers: [
      { image: IMAGES.vLibras, text: "v", correct: false },
      { image: IMAGES.wLibras, text: "w", correct: false },
      { image: IMAGES.uLibras, text: "u", correct: true },
    ]
  },
  {
    question: 'Encontre a carta que representa a letra: Z',
    answers: [
      { image: IMAGES.xLibras, text: "x", correct: false },
      { image: IMAGES.zLibras, text: "z", correct: true },
      { image: IMAGES.yLibras, text: "y", correct: true },
    ]
  },
  {
    question: 'Encontre a carta que representa a letra: Q',
    answers: [
      { image: IMAGES.eLibras, text: "e", correct: true },
      { image: IMAGES.tLibras, text: "t", correct: false },
      { image: IMAGES.qLibras, text: "q", correct: true },
    ]
  },
  {
    question: 'Encontre a carta que representa a letra: K',
    answers: [
      { image: IMAGES.nLibras, text: "n", correct: false },
      { image: IMAGES.kLibras, text: "k", correct: true },
      { image: IMAGES.jLibras, text: "j", correct: false },
    ]
  },
]