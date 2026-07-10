import { startStimulusApp } from '@symfony/stimulus-bundle';
import CsrfProtectionController from './controllers/csrf_protection_controller.js';
import OtpController from './controllers/otp_controller.js';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
app.register('csrf-protection', CsrfProtectionController);
app.register('otp', OtpController);
