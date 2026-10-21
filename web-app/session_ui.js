(() => {
  "use strict";
  window.FccFormControls = window.FccFormControls || { configure: function(el) { return el; } };
  function node(tag, className = "", text = "") {
    const element = document.createElement(tag);
    if (className) element.className = className;
    element.textContent = text;
    return (window.FccFormControls && window.FccFormControls.configure) ? window.FccFormControls.configure(element) : element;
  }
  function button(label, className, action) {
    const element = node("button", className, label);
    element.type = "button";
    element.addEventListener("click", action);
    return element;
  }
  function libraryHeader(title, description, create) {
    const header = node("header", "session-library-header"),
      copy = node("div");
    copy.append(node("h2", "", title), node("p", "", description));
    header.append(copy, create);
    return header;
  }
  function search(label, value, onChange) {
    const input = node("input", "session-search");
    input.type = "search";
    input.placeholder = label;
    input.value = value;
    input.setAttribute("aria-label", label);
    let timer;
    input.addEventListener("input", () => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        if (input.isConnected) onChange(input.value);
      }, 200);
    });
    return input;
  }
  function card(title, preview, meta, action) {
    const element = button("", "session-card", action);
    element.append(
      node("strong", "", title),
      node("p", "", preview),
      node("span", "", meta),
    );
    return element;
  }
  function shell(header, notice, transcript, composer) {
    const element = node("div", "session-shell");
    element.append(header, notice, transcript, composer);
    return element;
  }
  function header(
    backLabel,
    back,
    titleValue,
    titleLabel,
    onTitle,
    actions,
    controls,
  ) {
    const element = node("header", "session-header"),
      row = node("div", "session-header-row");
    const title = node("input", "session-title");
    title.value = titleValue;
    title.maxLength = 200;
    title.setAttribute("aria-label", titleLabel);
    title.addEventListener("blur", () => {
      if (title.isConnected) onTitle(title);
    });
    title.addEventListener("keydown", (event) => {
      if (event.key === "Enter" && !event.isComposing) title.blur();
    });
    row.append(
      button(backLabel, "session-back-button", back),
      title,
      ...actions,
    );
    element.append(row, controls);
    return element;
  }
  function modelControl(id, value, values, registry, onSelect) {
    const group = node("div", "session-control session-model-control"),
      label = node("label", "", "Model");
    label.htmlFor = id;
    const input = node("input", "session-model-input");
    input.id = id;
    input.type = "text";
    input.autocomplete = "off";
    input.value = value;
    input.setAttribute("aria-label", "Selected model");
    let committed = value,
      optionsSignature = JSON.stringify(values());
    const combobox = new window.FccModelCombobox(input, {
      listboxId: `${id}-options`,
      label: "model",
      values,
      registry,
      emptyMessage: () =>
        values().length ? "No matching models." : "No models available.",
      onSelect: (model) => {
        committed = model;
        onSelect(model);
      },
      onClose: () => {
        input.value = committed;
      },
    });
    group.append(label, combobox.element);
    return {
      group,
      input,
      combobox,
      update(model, disabled) {
        committed = model;
        if (!combobox.isOpen) input.value = model;
        input.disabled = disabled;
        combobox.toggle.disabled = disabled;
        if (disabled) combobox.close();
        const signature = JSON.stringify(values());
        if (combobox.isOpen && signature !== optionsSignature) {
          const active = combobox.visibleOptions[combobox.activeIndex]?.dataset.value;
          combobox.render(combobox.query);
          const index = combobox.visibleOptions.findIndex((option) => option.dataset.value === active);
          if (index >= 0) combobox.setActive(index, false);
        }
        optionsSignature = signature;
      },
    };
  }
  function selectControl(id, title, options, value, onChange) {
    const group = node("label", "session-control"),
      select = node("select");
    select.id = id;
    const update = (choices, selected) => {
      const signature = JSON.stringify(choices);
      if (select.dataset.options !== signature) {
        select.replaceChildren(
          ...choices.map(([value, label]) => new Option(label, value)),
        );
        select.dataset.options = signature;
      }
      select.value = selected;
    };
    update(options, value);
    select.addEventListener("change", () => onChange(select.value));
    group.append(node("span", "", title), select);
    return { group, select, update };
  }
  function composer(id, draft, placeholder, onInput, onSend, onStop) {
    const element = node("div", "session-composer"),
      textarea = node("textarea");
    textarea.id = `${id}Composer`;
    textarea.rows = 2;
    textarea.value = draft;
    textarea.placeholder = placeholder;
    textarea.setAttribute("aria-label", "Message");
    const actions = node("div", "session-composer-actions");

    // Voice Dictation (Speech-to-Text)
    const voiceBtn = node("button", "session-mic-button");
    voiceBtn.type = "button";
    voiceBtn.title = "Voice dictation";
    voiceBtn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg>`;

    let recognition = null;
    let isListening = false;
    let baseText = "";
    voiceBtn.addEventListener("click", () => {
      const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SpeechRec) {
        if (window.showClaudeToast) window.showClaudeToast("Speech recognition not supported in this browser.", "warning");
        return;
      }
      if (isListening && recognition) {
        try { recognition.stop(); } catch(_) {}
        isListening = false;
        voiceBtn.classList.remove("recording");
        return;
      }
      try {
        recognition = new SpeechRec();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = "en-US";
        baseText = textarea.value.trim();
        recognition.onstart = () => {
          isListening = true;
          voiceBtn.classList.add("recording");
          if (window.showClaudeToast) window.showClaudeToast("🎙️ Listening... speak prompt", "info");
        };
        recognition.onresult = (e) => {
          let fin = "", inter = "";
          for (let i = 0; i < e.results.length; ++i) {
            if (e.results[i].isFinal) fin += e.results[i][0].transcript + " ";
            else inter += e.results[i][0].transcript;
          }
          textarea.value = [baseText, fin.trim(), inter.trim()].filter(Boolean).join(" ");
          resizeComposer(textarea);
          onInput(textarea.value);
        };
        recognition.onerror = () => {
          isListening = false;
          voiceBtn.classList.remove("recording");
        };
        recognition.onend = () => {
          isListening = false;
          voiceBtn.classList.remove("recording");
        };
        recognition.start();
      } catch (err) {
        console.warn("Speech dictation error:", err);
        isListening = false;
        voiceBtn.classList.remove("recording");
      }
    });

    // Voice Readout Narration Toggle
    const soundwaveBtn = node("button", "session-soundwave-button");
    soundwaveBtn.type = "button";
    soundwaveBtn.title = "Toggle voice readout";
    soundwaveBtn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5v14M7 8v8M2 11v2M22 11v2"/></svg>`;
    soundwaveBtn.addEventListener("click", () => {
      if (typeof window.toggleVoiceNarration === "function") {
        window.toggleVoiceNarration();
      } else if ("speechSynthesis" in window) {
        if (window.speechSynthesis.speaking) {
          window.speechSynthesis.cancel();
          soundwaveBtn.classList.remove("active");
        } else {
          soundwaveBtn.classList.add("active");
        }
      }
    });

    const send = button("Send", "primary-button", onSend),
      stop = button("Stop", "danger-button", onStop);
    send.id = `${id}Send`;
    stop.id = `${id}Stop`;
    stop.hidden = true;
    textarea.addEventListener("input", () => {
      resizeComposer(textarea);
      onInput(textarea.value);
    });
    textarea.addEventListener("keydown", (event) => {
      if (event.key === "Enter" && !event.shiftKey && !event.isComposing) {
        event.preventDefault();
        if (!send.disabled && !send.hidden) onSend();
      }
    });
    actions.append(voiceBtn, soundwaveBtn, send, stop);
    element.append(textarea, actions);
    return element;
  }
  function message(role, label) {
    const element = node("article", `session-message ${role}-message`);
    const headerRow = node("div", "session-message-header-row");
    headerRow.append(node("div", "session-message-label", label));
    if (role === "assistant") {
      const speakBtn = node("button", "session-speak-btn");
      speakBtn.type = "button";
      speakBtn.title = "Read aloud";
      speakBtn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>`;
      speakBtn.addEventListener("click", () => {
        const text = element.innerText.replace(label, "").trim();
        if (!text) return;
        if (typeof window.toggleCaribSpeech === "function") {
          window.toggleCaribSpeech(text, speakBtn);
        } else if ("speechSynthesis" in window) {
          if (window.speechSynthesis.speaking || window.speechSynthesis.pending) {
            window.speechSynthesis.cancel();
            speakBtn.classList.remove("speaking");
          } else {
            try {
              window.speechSynthesis.resume();
              const cleanText = text
                .replace(/```[\s\S]*?```/g, " code block omitted. ")
                .replace(/`([^`]+)`/g, "$1")
                .replace(/#+\s+/g, "")
                .replace(/\*\*([^*]+)\*\*/g, "$1")
                .replace(/\*([^*]+)\*/g, "$1")
                .replace(/\[([^\]]+)\]\([^)]+\)/g, "$1")
                .replace(/<[^>]+>/g, " ")
                .replace(/\s+/g, " ")
                .trim();
              const u = new SpeechSynthesisUtterance(cleanText);
              u.rate = 1.0;
              u.pitch = 0.97;
              u.volume = 1.0;

              const voices = window.speechSynthesis.getVoices() || [];
              let preferred = null;
              if (typeof window.selectSovereignMaleVoice === "function") {
                preferred = window.selectSovereignMaleVoice(voices);
              } else {
                preferred = voices.find(v => (v.name.includes("UK English Male") || v.name.includes("Daniel") || v.name.includes("Reed") || v.name.includes("Eddy") || v.name.includes("Alex") || v.name.includes("David")) && v.lang.startsWith("en"));
              }
              if (preferred) u.voice = preferred;

              u.onstart = () => speakBtn.classList.add("speaking");
              u.onend = () => speakBtn.classList.remove("speaking");
              u.onerror = () => speakBtn.classList.remove("speaking");
              window.speechSynthesis.speak(u);
            } catch (err) {
              console.warn("Speech synthesis error:", err);
              speakBtn.classList.remove("speaking");
            }
          }
        }
      });
      headerRow.append(speakBtn);
    }
    element.append(headerRow);
    return element;
  }
  function thinking() {
    const element = node("details", "session-thinking");
    element.append(node("summary", "", "Thinking"));
    return element;
  }
  function resizeComposer(textarea) {
    textarea.style.height = "auto";
    textarea.style.height = `${textarea.scrollHeight}px`;
    textarea.style.overflowY =
      textarea.scrollHeight > textarea.clientHeight ? "auto" : "hidden";
  }
  function nearBottom(scroller) {
    return (
      scroller.scrollHeight - scroller.scrollTop - scroller.clientHeight < 80
    );
  }
  window.SessionUI = {
    libraryHeader,
    search,
    card,
    shell,
    header,
    modelControl,
    selectControl,
    composer,
    message,
    thinking,
    resizeComposer,
    nearBottom,
  };
})();
