import { useStateStore } from "@/login/components/store.js";
import axios from "axios";

var state = null;

const http = axios.create({});

function getState() {
    if (state == null) {
        state = useStateStore();
    }
    return state;
}

export const baseProps = ['module', 'authRequest', 'customerstyle', 'lonely', 'active'];

export function isActive() {
    return this.active || this.urlActive;
}

export function isIncomplete(authRequest, module) {
    return authRequest.info.inc && authRequest.info.inc.module == module.id;
}

export function getIncompleteModuleState(authRequest, module) {
    return (isIncomplete(authRequest, module) ? authRequest.info.inc.moduleState : {}) || {}
}

export function activate(module) {
    getState().activeModule = module.id;

}

export function deactivate() {
    getState().activeModule = null;
}

export function overview() {
    getState().activeModule = null;
}

export function request(module, authRequest, data) {
    return new Promise((resolve, reject) => {

        if (!authRequest || !authRequest.stateId) {
            reject('not authrequest provided or invalid state');
        }

        http.post(authRequest.info.api.replace('NAME_OF_THE_MODULE', typeof module === 'object' ? module.id : module), data, {
            headers: {
                "X-StateId": authRequest.stateId
            }
        }).then(
            response => {

                console.log(response.headers);
                updateAuthRequest(response.headers.get("x-authRequest"));

                resolve(response);

            }

        ).catch(error => {
            if (error.response.status == 403) {
                getState().error(
                    "You don't have permissions to access this app."
                );
            } else if (error.response.status == 404) {
                document.location = this.authRequest.info.nok;
            }
            updateAuthRequest(error.response.headers.get("x-authRequest"));
            reject(error);

        });
    });
}

function b64DecodeUnicode (str){
    return decodeURIComponent(
        Array.prototype.map
            .call(atob(str), function (c) {
                return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
            })
            .join("")
    );
}

function updateAuthRequest(authRequestRaw) {

    const state = useStateStore();

    var authRequest = JSON.parse(b64DecodeUnicode(authRequestRaw));

    console.log(authRequest);

    if (authRequest && authRequest.info.don) {

        post(authRequest.info.fin, {
            'authRequest': authRequest.stateId
        });

    } else if (authRequest.next == null) {

        state.authRequest = authRequest;
    } else {
        state.authRequest = authRequest;
    }

}


export function post(path, params, method) {

    var method = method || "post";

    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for (var key in params) {
        if (params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
        }
    }

    document.body.appendChild(form);
    form.submit();

}
