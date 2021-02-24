const testConfigResult = async() => {
  const formData = new FormData();
  formData.append('keyFileContents', document.querySelector('#settings-keyFileContents').value);
  formData.append('spreadsheetId', document.querySelector('#settings-spreadSheetId').value);
  formData.append('sheetName', document.querySelector('#settings-sheetContentTabName').value);

  const fetchResponse = await fetch(`/admin/spreadsheet-translations/settings/test-config`, {
    method: 'POST',
    body: formData,
  });
  return await fetchResponse.json();
};

const init = () => {
  const $spinner = document.querySelector('.js-test-config-spinner');
  const $testConfigButton = document.querySelector('.js-test-config-button');
  const $testResultConfig = document.querySelector('.js-test-config-result');

  $testConfigButton.addEventListener('click', async (event) => {
    $testConfigButton.classList.add('disabled');
    $spinner.classList.remove('hidden');
    $testResultConfig.innerHTML = '';

    const resultData = await testConfigResult();
    $spinner.classList.add('hidden');

    if (resultData.success) {
      $testResultConfig.classList.remove('error');
      $testResultConfig.classList.add('success');
      $testResultConfig.innerText = "Success!";
    } else {
      $testResultConfig.classList.remove('success');
      $testResultConfig.classList.add('error');
      $testResultConfig.innerText = "Error! \n" + resultData.message + "\n" + "Class: " + resultData.exception_class;
    }
    $testConfigButton.classList.remove('disabled');
  });
};

window.addEventListener('load', init);
