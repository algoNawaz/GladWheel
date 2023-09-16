const carousel = document.querySelector(".carousel");


function setupCarousel() {
  const contents = Object.values(
    carousel.querySelector(".carousel-content-wrapper").children
  );
  carousel.dataset.length = contents.length;
  const buttons = carousel.querySelector(".carousel-buttons");

  contents.forEach((content, index) => {
    if (index === 0) {
      content.dataset.state = "active";
    }
    content.dataset.index = index;
    content.style.setProperty("--carousel-index", index);
    content.className = "carousel-content";
    let button = document.createElement("button");
    button.classList.add("carousel-button");
    button.style.setProperty("--carousol-content-index", index);
    button.dataset.active = index === 0;
    button.dataset.content = index;
    buttons.append(button);
  });
}

function fromLeft(currentContent, nextContentIndex) {
  const nextSlide = carousel.querySelector(
    `.carousel-content[data-index='${nextContentIndex}']`
  );
  currentContent.dataset.state = "leave-right";
  nextSlide.dataset.state = "before-left-enter";
  setTimeout(() => {
    nextSlide.dataset.state = "active";
  });
  carousel.querySelector(
    `.carousel-button[data-content='${nextContentIndex}']`
  ).dataset.active = true;
}
function fromRight(currentContent, nextContentIndex) {
  const nextSlide = carousel.querySelector(
    `.carousel-content[data-index='${nextContentIndex}']`
  );
  currentContent.dataset.state = "leave-left";
  nextSlide.dataset.state = "before-right-enter";
  setTimeout(() => {
    nextSlide.dataset.state = "active";
  });
  carousel.querySelector(
    `.carousel-button[data-content='${nextContentIndex}']`
  ).dataset.active = true;
}

carousel.querySelector(".arrow-left").addEventListener("click", () => {
  const currentContent = carousel.querySelector(
    ".carousel-content[data-state='active']"
  );
  const total = parseInt(carousel.dataset.length);
  let currentNumber = parseInt(currentContent.dataset.index);
  let nextSlideIndex = (currentNumber + total - 1) % total;
  carousel.querySelector(
    `.carousel-button[data-active="true"]`
  ).dataset.active = false;
  fromLeft(currentContent, nextSlideIndex);
});

carousel.querySelector(".arrow-right").addEventListener("click", () => {
  const currentContent = carousel.querySelector(
    ".carousel-content[data-state='active']"
  );
  const total = parseInt(carousel.dataset.length);
  let currentNumber = parseInt(currentContent.dataset.index);
  let nextSlideIndex = (currentNumber + total + 1) % total;
  carousel.querySelector(
    `.carousel-button[data-active="true"]`
  ).dataset.active = false;
  fromRight(currentContent, nextSlideIndex);
});
setupCarousel();

carousel.querySelectorAll(".carousel-button").forEach((button) => {
  button.addEventListener("click", () => {
    const currentContent = carousel.querySelector(
      ".carousel-content[data-state='active']"
    );
    carousel.querySelector(
      `.carousel-button[data-active="true"]`
    ).dataset.active = false;
    let currentNumber = parseInt(currentContent.dataset.index);
    let buttonNumber = parseInt(button.dataset.content);
    if (currentNumber === buttonNumber) return;
    else if (currentNumber > buttonNumber) {
      fromLeft(currentContent, buttonNumber);
    } else {
      fromRight(currentContent, buttonNumber);
    }
  });
});
