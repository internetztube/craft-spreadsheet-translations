import utilityButtonEventListener from './_utility-button-event-listener';

const init = () => {
  const $trigger = document.querySelector('.js-utility-push-handles');
  const $progressContainer = document.querySelector('.js-utility-push-handles-status');

  utilityButtonEventListener($trigger, $progressContainer, async (done) => {
    await fetch(`/admin/spreadsheet-translations/utilities/push-handles`, { method: 'POST' });
    done();
  });
};

export default {
  init,
};