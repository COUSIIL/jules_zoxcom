<template>
  <div class="svg-wrapper">
    <div class="rect rect-1"></div>
    <div class="rect rect-2"></div>
    <svg class="svg-on-top" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800">
      <g class="animated-square">
        <path
          class="clss-1"
          d="M740.68,570.91v154.15s-163.48,0-163.48,0c17.39-49.83,17.21-104.43-.55-154.15h164.03Z"
        />
      </g>
    </svg>
    
    
  </div>
</template>

<style scoped>
.svg-wrapper {
  position: relative;
  width: 100%;
  max-width: 300px;
  aspect-ratio: 1;
  overflow: visible;
}

.svg-on-top {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 10;
  width: 100%;
  height: 100%;
  z-index: 5;
  pointer-events: none; /* important si tu veux interagir avec ce qui est dessous */
}

/* RECTANGLES ANIMÉS EN CSS */
.rect {
  position: absolute;
  height: 19%; /* relative à svg-wrapper */
  background-color: #FCEFFF;
  visibility: hidden;
}

/* Premier rectangle (horizontal, adaptatif) */
.rect-1 {
  top: 8%;
  left: 10%;
  width: 0;
  animation: growWidth1 5s cubic-bezier(0.7, 0, 0.3, 1) infinite;
  animation-delay: 0s;
}

/* Deuxième rectangle (en diagonale) */
.rect-2 {
  top: 4.5%;
  left: 88%;
  width: 0;
  transform-origin: 0 50%;
  transform: rotate(135deg);
  animation: growWidth2 5s cubic-bezier(0.7, 0, 0.3, 1) infinite;
  animation-delay: 0s;
}

/* Adapté à 100% de la largeur du container */
@keyframes growWidth1 {
  0% {
    width: 0%;
    visibility: visible;
    opacity: 0;
  }
  10% {
    width: 72%;
    opacity: 1;
  }
  48% {
    width: 72%;
    visibility: visible;
    opacity: 1;
  }
  50% {
    width: 72%;
    visibility: visible;
    opacity: 0.5;
  }
  52% {
    width: 72%;
    visibility: visible;
    opacity: 1;
  }
  90% {
    width: 72%;
    visibility: visible;
  }


  100% {
    width: 0%;
    visibility: hidden;
  }
}

@keyframes growWidth2 {
  0% {
    width: 0%;
    visibility: visible;
    opacity: 0;
  }
  12% {
    width: 0%;
    opacity: 0;
  }
  25% {
    width: 100%;
    opacity: 1;
  }
  49% {
    width: 100%;
    opacity: 1;
  }
  51% {
    width: 100%;
    opacity: 0.5;
  }
  53% {
    width: 100%;
    opacity: 1;
  }
  70% {
    width: 100%;
  }
  90% {
    width: 0%;
  }
  100% {
    width: 0%;
    visibility: hidden;
  }
}

/* Carré orange animé */
.clss-1 {
  fill: #f3931a;
  transform-origin: center;
  animation: shine 2.5s infinite;
}

@keyframes shine {
  0%, 80% {
    filter: brightness(1);
  }
  85% {
    filter: brightness(2.5);
  }
  100% {
    filter: brightness(1);
  }
}

.animated-square {
  animation: slideInFromLeft 5s cubic-bezier(0.7, 0, 0.3, 1) infinite;
  transform-box: fill-box;
  transform-origin: center;
}

@keyframes slideInFromLeft {
  0% {
    transform: translate(-320%, -320%);
    opacity: 0;
  }
  12.5% {
    transform: translate(10%, -320%) rotate(135deg);
    opacity: 1;
  }
  25% {
    transform: translate(-290%, -10%) rotate(135deg);
    opacity: 1;
  }
  50% {
    transform: translate(0%, 0%) rotate(0deg);
    opacity: 1;
  }
  72% {
    transform: translate(-290%, -10%) rotate(135deg);
    opacity: 1;
  }
  88% {
    transform: translate(10%, -320%) rotate(135deg);
    opacity: 1;
  }
  100% {
    transform: translate(-320%, -320%);
    opacity: 0;
  }
}
</style>
