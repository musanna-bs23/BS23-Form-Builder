import { render, screen, fireEvent } from '@testing-library/react';
import SettingsPanel from '../components/SettingsPanel';
import { defaultSettings } from '../settings-api';

test('settings panel renders notification and confirmation controls', () => {
  render(<SettingsPanel formId={1} settings={defaultSettings()} status="" onChange={() => {}} onSave={() => {}} onTest={() => {}} />);

  expect(screen.getByText('Email notification')).not.toBeNull();
  expect(screen.getByText('Confirmation')).not.toBeNull();
});

test('settings panel emits changed notification payload', () => {
  const onChange = jest.fn();
  render(<SettingsPanel formId={1} settings={defaultSettings()} status="" onChange={onChange} onSave={() => {}} onTest={() => {}} />);

  fireEvent.change(screen.getByDisplayValue('{admin_email}'), { target: { value: 'owner@example.com' } });

  expect(onChange).toHaveBeenCalledWith(expect.objectContaining({
    notification: expect.objectContaining({ to: 'owner@example.com' }),
  }));
});

test('settings panel calls test email action', () => {
  const onTest = jest.fn();
  render(<SettingsPanel formId={1} settings={defaultSettings()} status="" onChange={() => {}} onSave={() => {}} onTest={onTest} />);

  fireEvent.click(screen.getByText('Send Test'));

  expect(onTest).toHaveBeenCalled();
});

test('settings panel emits changed style payload', () => {
  const onChange = jest.fn();
  render(<SettingsPanel formId={1} settings={defaultSettings()} status="" onChange={onChange} onSave={() => {}} onTest={() => {}} />);

  fireEvent.change(screen.getByLabelText('Form width'), { target: { value: '900px' } });
  fireEvent.change(screen.getByLabelText('Button background'), { target: { value: '#111827' } });

  expect(onChange).toHaveBeenCalledWith(expect.objectContaining({
    style: expect.objectContaining({ max_width: '900px' }),
  }));
  expect(onChange).toHaveBeenCalledWith(expect.objectContaining({
    style: expect.objectContaining({ button_background: '#111827' }),
  }));
});
