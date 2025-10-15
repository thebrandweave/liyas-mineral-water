<section class="hollow-word-section d-flex justify-content-center align-items-center">
  <div class="hollow-word" id="hollow">
    <h1 class="hollow-text-content">
      <span class="outline">L</span>
      <span class="outline">I</span>
      <span class="outline">Y</span>
      <span class="outline">A</span>
      <span class="outline">S</span>
    </h1>
  </div>
</section>

<style>
/* Full-width and partial height section */
.hollow-word-section {
  width: 100vw;
  height: 35vh;
  background: #ffffff; /* background for contrast */
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  margin-top: -5em;
}

/* Container */
.hollow-word {
  width: 100%;
  text-align: center;
}

/* Text container */
.hollow-text-content {
  width: 100%;
  display: flex;
  justify-content: space-evenly; /* evenly space letters */
  align-items: center;
  font-size: 21vw; /* responsive font size */
  font-weight: 900;
  text-transform: uppercase;
  margin: 0;
  padding: 0;
  line-height: 1;
  letter-spacing: -0.2em;
}

/* Masked shimmer effect on letters */
.outline {
  flex: 1;
  text-align: center;
  color: transparent;
  background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e');
  background-size: 200%;
  background-repeat: repeat;
  background-position: 0 0;
  -webkit-background-clip: text;
  background-clip: text;
  animation: animate-background 4s linear infinite;
}

/* Animate background shimmer */
@keyframes animate-background {
  0% {
    background-position: 0% 50%;
  }
  100% {
    background-position: 200% 50%;
  }
}
</style>
