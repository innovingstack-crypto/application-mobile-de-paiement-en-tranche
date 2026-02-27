export function validatePhone(phone: string): boolean {
  return /^(237)?6[0-9]{8}$/.test(phone.replace(/\s/g, ''));
}

export function validateEmail(email: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
