import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["input"];
    static values = {
        timeout: Number,
        resendDelay: Number,
        requestUrl: String,
        submitUrl: String,
    };

    connect() {
        this.inputs = this.inputTargets;
        this.hiddenInput = document.getElementById("confirmation_code_code");

        this.inputs.forEach((input, index) => {
            input.addEventListener("input", () => this.handleInput(index));
            input.addEventListener("keydown", (e) =>
                this.handleKeyDown(e, index),
            );
            input.addEventListener("paste", (e) => this.handlePaste(e));
        });

        if (this.inputs.length) this.inputs[0].focus();
        this.startTimers();
    }

    handleInput(index) {
        const val = this.inputs[index].value;
        if (val.length === 1 && index < 5) this.inputs[index + 1].focus();
        this.updateHiddenInput();
        this.updateVisualState();

        // Auto-submit si 6 chiffres saisis
        const fullCode = this.inputs.map((i) => i.value).join("");
        if (fullCode.length === 6) {
            document.getElementById("otp-form").submit();
        }
    }

    handleKeyDown(e, index) {
        if (
            e.key === "Backspace" &&
            this.inputs[index].value === "" &&
            index > 0
        ) {
            this.inputs[index - 1].focus();
        }
    }

    handlePaste(e) {
        e.preventDefault();
        const paste = e.clipboardData.getData("text").trim();
        if (/^\d{6}$/.test(paste)) {
            paste.split("").forEach((char, i) => {
                if (this.inputs[i]) this.inputs[i].value = char;
            });
            this.updateHiddenInput();
            if (this.inputs[5]) this.inputs[5].focus();
            setTimeout(() => document.getElementById("otp-form").submit(), 100);
        }
    }

    updateHiddenInput() {
        const code = this.inputs.map((i) => i.value).join("");
        if (this.hiddenInput) this.hiddenInput.value = code;
    }

    updateVisualState() {
        this.inputs.forEach((input) => {
            if (input.value) input.classList.add("filled");
            else input.classList.remove("filled");
        });
    }

    startTimers() {
        // Timer principal (15 min)
        let secondsLeft = this.timeoutValue;
        const timerElem = document.getElementById("timer");
        const interval = setInterval(() => {
            if (secondsLeft <= 0) {
                clearInterval(interval);
                timerElem.textContent = "";
            } else {
                const min = Math.floor(secondsLeft / 60);
                const sec = secondsLeft % 60;
                timerElem.textContent = `${min}:${sec.toString().padStart(2, "0")}`;
                secondsLeft--;
            }
        }, 1000);

        // Cooldown pour renvoyer le code (60 sec)
        let cooldown = this.resendDelayValue;
        const resendLink = document.getElementById("resend-link");
        const disableInterval = setInterval(() => {
            if (cooldown <= 0) {
                clearInterval(disableInterval);
                if (resendLink) {
                    resendLink.classList.remove("disabled");
                    resendLink.style.pointerEvents = "auto";
                }
            } else if (resendLink) {
                resendLink.classList.add("disabled");
                resendLink.style.pointerEvents = "none";
                cooldown--;
            }
        }, 1000);
    }

    async resend(event) {
        event.preventDefault();
        const link = event.currentTarget;
        if (link.classList.contains("disabled")) return;

        try {
            const response = await fetch(this.requestUrlValue, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    _token: document.querySelector('input[name="_token"]')
                        .value,
                }),
            });
            if (response.ok) {
                alert("Code renvoyé !");
                window.location.reload(); // Pour réinitialiser le timer et le cooldown
            } else {
                const data = await response.json();
                alert(data.error || "Erreur lors du renvoi");
            }
        } catch (e) {
            alert("Erreur réseau");
        }
    }
}
