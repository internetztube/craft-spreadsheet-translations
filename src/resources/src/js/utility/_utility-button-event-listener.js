const eventListener = ($button, $progressContainer, callback) => {
  const progressBar = new Craft.ProgressBar($progressContainer);
  const $progressBar = progressBar.$progressBar[0];
  $progressBar.classList.remove('hidden');
  const $alldone = $progressContainer.querySelector('.alldone');

  const done = () => {
    const width = 100;
    progressBar.setProgressPercentage(width);
    $progressBar.classList.remove('active');
    setTimeout(() => {
      $alldone.classList.add('active');
    }, 500);
  };

  const start = async () => {
    return new Promise((resolve) => {
      progressBar.resetProgressBar();
      $alldone.classList.remove('active');
      setTimeout(() => {
        $progressBar.classList.add('active');
        resolve();
      }, 250);
    })
  };


  $button.addEventListener('click', async () => {
    await start();
    callback(done);
  });

};

export default eventListener;