import { render, screen, fireEvent } from '@testing-library/react';
import FieldSettingsPanel from '../components/FieldSettingsPanel';

const field = {
  id: 'field_1',
  type: 'dropdown',
  label: 'Department',
  name: 'department',
  required: false,
  settings: { choices: ['Sales', 'Support'] },
};

test('field settings panel renders empty state', () => {
  render(<FieldSettingsPanel field={null} />);

  expect(screen.getByText('Select a field from the canvas to edit it.')).not.toBeNull();
});

test('field settings panel emits label and choice updates', () => {
  const onUpdate = jest.fn();
  const onUpdateSettings = jest.fn();
  render(<FieldSettingsPanel field={field} onUpdate={onUpdate} onUpdateSettings={onUpdateSettings} onDelete={() => {}} onDuplicate={() => {}} onMove={() => {}} />);

  fireEvent.change(screen.getByDisplayValue('Department'), { target: { value: 'Team' } });
  fireEvent.change(screen.getByLabelText('Choices'), { target: { value: 'A\nB' } });

  expect(onUpdate).toHaveBeenCalledWith('field_1', { label: 'Team' });
  expect(onUpdateSettings).toHaveBeenCalledWith('field_1', { choices: ['A', 'B'] });
});

test('field settings panel calls duplicate delete and move actions', () => {
  const onDelete = jest.fn();
  const onDuplicate = jest.fn();
  const onMove = jest.fn();
  render(<FieldSettingsPanel field={field} onUpdate={() => {}} onUpdateSettings={() => {}} onDelete={onDelete} onDuplicate={onDuplicate} onMove={onMove} />);

  fireEvent.click(screen.getByText('Duplicate'));
  fireEvent.click(screen.getByText('Delete'));
  fireEvent.click(screen.getByText('Up'));

  expect(onDuplicate).toHaveBeenCalledWith('field_1');
  expect(onDelete).toHaveBeenCalledWith('field_1');
  expect(onMove).toHaveBeenCalledWith('field_1', 'up');
});
