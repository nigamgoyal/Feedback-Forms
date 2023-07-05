// to add display block on form on click of give feedback button

function showHideData(dataId, requestId) {
  const data = document.getElementById(dataId);
  const result = document.getElementById(requestId)
  if (data.style.display === "none") {
    data.style.display = "block";
    result.style.display = "none"
  }
}




function addNametoInputHidden(selectName, hiddenInput) {
  document.querySelector(selectName).addEventListener('change', function () {
    const displayName = this.options[this.selectedIndex].getAttribute('data-display-name');
    document.querySelector(hiddenInput).value = displayName;
  });
}
// addNametoInputHidden('#empid', '#empname');

// capital first letter of sentence 
function capitalizeFirstLetter(sentence) {
  if (typeof sentence !== 'string' || sentence.length === 0) {
    return sentence;
  }

  return sentence.charAt(0).toUpperCase() + sentence.slice(1);
}

function showTable(tableId) {
  // Get all tables
  const tables = document.querySelectorAll('[id^="table_data_"]');
  tables.forEach(table => {
    if (table.id === tableId) {
      table.classList.remove('d-none');
    } else {
      table.classList.add('d-none');
    }
  });
}

function futureDate() {
  // only future date is allowed to select
  var meetingDateInput = document.getElementById('meetingDate');

  var currentDate = new Date().toISOString().split('T')[0];
  meetingDateInput.setAttribute('min', currentDate);
}

//to add input field on click
var actionItemCounter = 1;
var talkingPointCounter = 1;
var inputIds = [];
function addInput(elementId, fieldname) {
  let x = document.createElement("INPUT");
  let inputId;
  let classname;
  if (fieldname === 'actionItem') {
    inputId = fieldname + actionItemCounter;
    classname = 'actionItem';
    actionItemCounter++;
  }
  else if (fieldname === 'talkingPoint') {
    inputId = fieldname + talkingPointCounter;
    classname = 'agendaPoint';
    talkingPointCounter++;
  }

  x.setAttribute("type", "text");
  x.setAttribute("id", inputId);
  x.setAttribute("class", "mb-2 me-2 templateInputs " + classname);
  x.setAttribute("required", "");
  let parentElement = elementId.parentNode;
  parentElement.insertBefore(x, elementId.nextSibling);

  // Store the inputId in the inputIds array
  inputIds.push(inputId);

  // Create remove button
  var removeButton = document.createElement("span");
  removeButton.setAttribute("class", "removeInput float-end text-danger cursor");
  removeButton.innerHTML = "&#215;";
  removeButton.addEventListener("click", function () {
    removeInput(inputId);
    removeButton.remove();
  });
  // Append remove button with the input element
  parentElement.insertBefore(removeButton, elementId.nextSibling);
}


function removeInput(inputId) {
  var element = document.getElementById(inputId);
  if (element) {
    element.parentNode.removeChild(element);
    // Remove the inputId from the inputIds array
    var index = inputIds.indexOf(inputId);
    if (index > -1) {
      inputIds.splice(index, 1);
    }
  }
}


var talkingPointClicked = false;  // Track if addTalkingPointElement is clicked
function handleAddTalkingPointClick() {
  // Event listener for addTalkingPointElement click
  talkingPointClicked = true;
  addInput(this, 'talkingPoint');
}

var buttonElement = document.querySelector('.agendaBtn');
buttonElement.addEventListener('click', handleAddTalkingPointClick);


var regex = /^[A-Za-z0-9\s,.]+$/;
function templateForm(event) {
  event.preventDefault();

  let templateName = document.forms["createTemplate"]["topicName"].value;
  let nameError = document.getElementsByClassName('templateError')[0];
  if (!templateName && templateName === "") {
    nameError.classList.remove('d-none');
    return false;
  }
  // if (!regex.test(templateName)) {
  //   nameError.classList.remove('d-none');
  //   nameError.innerHTML = "Only alphabets and numbers are allowed";
  //   return false;
  // }

  // Handle the case when no Add talking Point is clicked
  let agendaError = document.getElementsByClassName('templateError')[1];
  if (talkingPointClicked === false) {
    agendaError.classList.remove('d-none');
    return false;
  }

  // Check dynamically created input tags
  let agendaValues = {};
  let agendaInputs = document.getElementsByClassName('agendaPoint');
  let atLeastOneAgenda = false;
  for (let i = 0; i < agendaInputs.length; i++) {
    let agenda = agendaInputs[i];
    if (!agenda.value || agenda.value.trim() === "") {
      agendaError.innerHTML = "Atleast one agenda must be filled out";
      atLeastOneAgenda = true;
      break;
    }
    else {
      if (!regex.test(agenda.value)) {
        agendaError.innerHTML = "Only alphabets and numbers are allowed";
        atLeastOneAgenda = true;
        break;
      }
      agendaError.innerHTML = "";
      agendaValues[agenda.id] = agenda.value;
    }
  }

  if (atLeastOneAgenda === true) {
    agendaError.classList.remove('d-none');
    return false;
  }


  let actionItems = {};
  let actionInputs = document.getElementsByClassName('actionItem');
  for (let i = 0; i < actionInputs.length; i++) {
    let action = actionInputs[i];
    actionItems[action.id] = action.value;
  }

  let templateCategory = document.getElementById('templateCategory1to1').value;
  let templateDescription = document.getElementById('templateDescription').value;

  let data_arr = {};
  data_arr.name = templateName;
  data_arr.agenda = agendaValues;
  data_arr.action_item = actionItems;
  data_arr.category = templateCategory;
  data_arr.description = templateDescription;

  if (Object.keys(data_arr.agenda).length < 1 || typeof data_arr.agenda !== 'object') {
    agendaError.innerHTML = "Atleast one agenda must be filled out";
    return false;
  }

  // data = JSON.stringify(data_arr);
  document.getElementById('meetingTemplateData').value = btoa(JSON.stringify(data_arr));
  document.querySelector('.btn-close').click();
}


function createCheckbox(value, id, className, labelclassName) {
  let inputElement = document.createElement('input');
  inputElement.type = 'checkbox';
  inputElement.setAttribute('id', id);
  inputElement.setAttribute('class', className);
  inputElement.value = value;
  let labelElement = document.createElement('label');
  labelElement.setAttribute('class', 'pb-1 ' + labelclassName);
  labelElement.appendChild(document.createTextNode(value));


  // Add event listener to checkbox
  inputElement.addEventListener('change', function () {
    if (this.checked) {
      // Apply strikethrough style when checkbox is checked
      labelElement.style.textDecoration = 'line-through';
      inputElement.setAttribute('checked', '');
    } else {
      // Remove strikethrough style when checkbox is unchecked
      labelElement.style.textDecoration = 'none';
      inputElement.removeAttribute('checked');
    }
  });

  recieved1_to_1_body.appendChild(inputElement);
  recieved1_to_1_body.appendChild(labelElement);
  let lineBreakElement = document.createElement('br');
  recieved1_to_1_body.appendChild(lineBreakElement);

}

var templateID;
function getData(templateId) {
  templateID = templateId;
  if (!templateID) return false;

  jQuery.ajax({
    url: '',
    type: 'GET',
    data: { 'templateId': templateID, "action": 'fetchTemplateDetails' },
    success: function (response) {
      var result_data = JSON.parse(response);
      var recieved1_to_1_body = document.getElementById('recieved1_to_1_body');
      if (result_data['message'] === 'Success' && Array.isArray(result_data['record'])) {
        recieved1_to_1_body.classList.remove("text-danger");
        recieved1_to_1_body.innerHTML = '';

        var divElement = document.createElement('div');
        divElement.innerHTML = result_data['record'];

        // Iterate over child elements of the div
        var childElements = divElement.children;
        for (var i = 0; i < childElements.length; i++) {
          var childElement = childElements[i];

          if (childElement.tagName === 'P') {
            var pContent = childElement.innerHTML.trim();

            if (pContent !== '') {
              var pElement = document.createElement('p');
              pElement.innerHTML = pContent;
              recieved1_to_1_body.appendChild(pElement);
            }
          } else if (childElement.tagName === 'SPAN') {
            var spanContent = childElement.innerHTML.trim();

            if (spanContent !== '') {
              var spanElement = document.createElement('span');
              spanElement.innerHTML = spanContent;
              recieved1_to_1_body.appendChild(spanElement);
            }
          }
          else if (childElement.tagName === 'TEXTAREA') {
            var textAreaContent = childElement.innerHTML.trim();

            var textAreaHeading = document.createElement('h6');

            textAreaHeading.innerHTML = 'Your shared notes';
            textAreaHeading.setAttribute('class', 'py-3');
            var textAreaElement = document.createElement('textarea');
            textAreaElement.setAttribute('class', 'w-100');
            recieved1_to_1_body.appendChild(textAreaHeading);
            if (textAreaContent) {
              textAreaElement.textContent = textAreaContent;
            }
            recieved1_to_1_body.appendChild(textAreaElement);
          }
        }

        // let meetingstatus = result_data['record'][2];
        let meetingstatus = document.getElementById('meetingStatus');
        if (meetingstatus) {
          let checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.setAttribute('class', 'tgl tgl-light');
          checkbox.setAttribute('id', 'userStatus')
          checkbox.value = meetingstatus.innerHTML;
          let label = document.createElement('label');
          label.setAttribute('class', 'tgl-btn');
          let spanChild = document.createElement('span');
          spanChild.innerHTML = " 1:1's " + meetingstatus.innerHTML.toUpperCase();

          if (meetingstatus.innerHTML === 'on') {
            checkbox.checked = true;
          } else {
            checkbox.checked = false;
          }

          meetingstatus.addEventListener('click', function () {
            var userStatus = document.getElementById('userStatus');
            if (userStatus.value === 'on') {
              userStatus.value = 'off';
            } else {
              userStatus.value = 'on';
            }
            spanChild.innerHTML = " 1:1's " + userStatus.value.toUpperCase();
            checkbox.checked = (userStatus.value === 'on');
          });

          meetingstatus.innerHTML = '';
          meetingstatus.appendChild(checkbox);
          meetingstatus.appendChild(label);
          meetingstatus.appendChild(spanChild);
        }

        let headingAgenda = document.createElement('h6');
        headingAgenda.innerHTML = 'Agenda';
        headingAgenda.setAttribute('class', 'py-3 border-bottom');
        recieved1_to_1_body.appendChild(headingAgenda);

        let agenda = result_data['record'][5];
        if (agenda) {
          for (var i = 0; i < agenda.length; i++) {
            let agendaPoint = agenda[i].talking_point;
            let agendaId = agenda[i].agenda_id;
            let agendaStatus = agenda[i].agenda_status;

            let value = capitalizeFirstLetter(agendaPoint);
            createCheckbox(value, agendaId, 'inputElementAgenda', 'agendaLabel');

            let agendaItems = document.getElementsByClassName('inputElementAgenda');
            let labelItems = document.getElementsByClassName('agendaLabel');
            if (agendaStatus === 'off') {
              labelItems[i].style.textDecoration = 'line-through';
              agendaItems[i].setAttribute('checked', '');
            }

          }


        }

        let addNewAgenda = document.createElement('div');
        addNewAgenda.setAttribute('class', 'form-text cursor');
        addNewAgenda.innerHTML = '+Add talking point';

        addNewAgenda.onclick = function () {
          addInput(this, 'talkingPoint');
          talkingPointCounter++;
        };
        recieved1_to_1_body.appendChild(addNewAgenda);

        let headingAction = document.createElement('h6');
        headingAction.innerHTML = 'Action Items';
        headingAction.setAttribute('class', 'py-3 border-bottom');
        recieved1_to_1_body.appendChild(headingAction);

        let action = result_data['record'][6];

        if (action) {
          for (var i = 0; i < action.length; i++) {
            let actionPoint = action[i].action_item;
            let actionId = action[i].action_id;
            let actionStatus = action[i].action_status;

            let value = capitalizeFirstLetter(actionPoint);
            createCheckbox(value, actionId, 'inputElementAction', 'actionLabel');

            let actionItems = document.getElementsByClassName('inputElementAction');
            let labelItems = document.getElementsByClassName('actionLabel');
            if (actionStatus === 'off') {
              labelItems[i].style.textDecoration = 'line-through';
              actionItems[i].setAttribute('checked', '');
            }
          }
        }

        let addNewAction = document.createElement('div');
        addNewAction.setAttribute('class', 'form-text cursor');
        addNewAction.innerHTML = '+Add action item';

        addNewAction.onclick = function () {
          addInput(this, 'actionItem');
          actionItemCounter++;
        };
        recieved1_to_1_body.appendChild(addNewAction);

      }
      else {
        recieved1_to_1_body.classList.add("text-danger");
        recieved1_to_1_body.innerHTML = 'No data found :(';
        return false;
      }
    }
  });


  const newTemplateData = document.getElementById('newTemplateData');
  newTemplateData.addEventListener('submit', handleTemplateNewData);

}


function handleTemplateNewData(e) {

  e.preventDefault();
  let agendaInputs = document.getElementsByClassName('agendaPoint');
  let agendaValues = {};
  for (let i = 0; i < agendaInputs.length; i++) {
    let agenda = agendaInputs[i];
    agendaValues[agenda.id] = agenda.value;
  }

  let actionInputs = document.getElementsByClassName('actionItem');
  let actionItems = {};
  for (let i = 0; i < actionInputs.length; i++) {
    let action = actionInputs[i];
    actionItems[action.id] = action.value;
  }

  let checkedAgendas = document.getElementsByClassName('inputElementAgenda');
  let AgendasChecked = false;
  let agendaStatus = 'on';
  let agendaCheckboxIds = [];
  for (let i = 0; i < checkedAgendas.length; i++) {
    if (checkedAgendas[i].checked) {
      agendaCheckboxIds.push(checkedAgendas[i].id);
      AgendasChecked = true;
    }
  }

  if (AgendasChecked === true) {
    agendaStatus = 'off';
  }

  let checkedActions = document.getElementsByClassName('inputElementAction');
  let ActionsChecked = false;
  let actionStatus = 'on';
  let actionCheckboxIds = [];
  for (let i = 0; i < checkedActions.length; i++) {
    if (checkedActions[i].checked) {
      actionCheckboxIds.push(checkedActions[i].id);
      ActionsChecked = true;
    }
  }
  if (ActionsChecked === true) {
    actionStatus = 'off';
  }

  let textarea = document.getElementsByTagName('textarea')[1];
  let description;
  if (textarea) {
    description = textarea.value;
  }

  // let meetingStatus = document.getElementById('userStatus');
  // if (meetingStatus.value.trim() === 'on') {
  let data_arr = {};
  data_arr = {
    templateId: templateID,
    userStatus: userStatus.value,
    description: description,
    agenda_id: agendaCheckboxIds,
    agenda: agendaValues,
    agenda_status: agendaStatus,
    action_id: actionCheckboxIds,
    action_item: actionItems,
    action_status: actionStatus
  };


  let data = JSON.stringify(data_arr);
  jQuery.ajax({
    url: '',
    type: 'POST',
    data: { 'meetingTemplateNewData': data, "action": 'existingTemplate' },
    success: function (response) {
      document.querySelector('.close-template').click();
    }
  });
  e.target.reset();
  // }
  // else return false;
}


