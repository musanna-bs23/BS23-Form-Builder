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

test('field settings panel enables and edits conditional logic', () => {
  const onUpdateSettings = jest.fn();
  render(
    <FieldSettingsPanel
      field={{ ...field, settings: {} }}
      fields={[
        { id: 'field_1', type: 'dropdown', label: 'Department', name: 'department', settings: {} },
        { id: 'field_2', type: 'email', label: 'Email', name: 'email', settings: {} },
      ]}
      onUpdate={() => {}}
      onUpdateSettings={onUpdateSettings}
      onDelete={() => {}}
      onDuplicate={() => {}}
      onMove={() => {}}
    />
  );

  fireEvent.click(screen.getByLabelText('Enable conditional logic'));
  expect(onUpdateSettings).toHaveBeenCalledWith('field_1', {
    conditionalLogic: {
      enabled: true,
      action: 'show',
      match: 'all',
      rules: [{ field: 'email', operator: 'equals', value: '' }],
    },
  });
});

test('field settings panel enables conditional logic with only one eligible field', () => {
  const onUpdateSettings = jest.fn();
  render(
    <FieldSettingsPanel
      field={{ ...field, settings: {} }}
      fields={[
        { id: 'field_1', type: 'dropdown', label: 'Department', name: 'department', settings: {} },
      ]}
      onUpdate={() => {}}
      onUpdateSettings={onUpdateSettings}
      onDelete={() => {}}
      onDuplicate={() => {}}
      onMove={() => {}}
    />
  );

  fireEvent.click(screen.getByLabelText('Enable conditional logic'));
  expect(onUpdateSettings).toHaveBeenCalledWith('field_1', {
    conditionalLogic: {
      enabled: true,
      action: 'show',
      match: 'all',
      rules: [{ field: 'department', operator: 'equals', value: '' }],
    },
  });
});

test('field settings panel updates conditional rule values', () => {
  const onUpdateSettings = jest.fn();
  render(
    <FieldSettingsPanel
      field={{
        ...field,
        settings: {
          conditionalLogic: {
            enabled: true,
            action: 'show',
            match: 'all',
            rules: [{ field: 'email', operator: 'equals', value: '' }],
          },
        },
      }}
      fields={[
        { id: 'field_1', type: 'dropdown', label: 'Department', name: 'department', settings: {} },
        { id: 'field_2', type: 'email', label: 'Email', name: 'email', settings: {} },
      ]}
      onUpdate={() => {}}
      onUpdateSettings={onUpdateSettings}
      onDelete={() => {}}
      onDuplicate={() => {}}
      onMove={() => {}}
    />
  );

  fireEvent.change(screen.getByLabelText('Conditional action'), { target: { value: 'hide' } });
  fireEvent.change(screen.getByLabelText('Rule value'), { target: { value: 'sales@example.com' } });

  expect(onUpdateSettings).toHaveBeenCalledWith('field_1', expect.objectContaining({
    conditionalLogic: expect.objectContaining({ action: 'hide' }),
  }));
  expect(onUpdateSettings).toHaveBeenCalledWith('field_1', expect.objectContaining({
    conditionalLogic: expect.objectContaining({
      rules: [{ field: 'email', operator: 'equals', value: 'sales@example.com' }],
    }),
  }));
});

test('field settings panel updates text validation settings', () => {
  const onUpdateSettings = jest.fn();
  render(
    <FieldSettingsPanel
      field={{ id: 'field_text', type: 'text', label: 'Code', name: 'code', settings: {} }}
      fields={[]}
      onUpdate={() => {}}
      onUpdateSettings={onUpdateSettings}
      onDelete={() => {}}
      onDuplicate={() => {}}
      onMove={() => {}}
    />
  );

  fireEvent.change(screen.getByLabelText('Min characters'), { target: { value: '3' } });
  fireEvent.change(screen.getByLabelText('Regex pattern'), { target: { value: '^[A-Z]+$' } });

  expect(onUpdateSettings).toHaveBeenCalledWith('field_text', {
    validation: { minLength: '3' },
  });
  expect(onUpdateSettings).toHaveBeenCalledWith('field_text', {
    validation: { pattern: '^[A-Z]+$' },
  });
});

test('field settings panel updates custom validation rules', () => {
  const onUpdateSettings = jest.fn();
  render(
    <FieldSettingsPanel
      field={{ id: 'field_text', type: 'text', label: 'Username', name: 'username', settings: { validation: { rules: 'required|string' } } }}
      fields={[]}
      onUpdate={() => {}}
      onUpdateSettings={onUpdateSettings}
      onDelete={() => {}}
      onDuplicate={() => {}}
      onMove={() => {}}
    />
  );

  fireEvent.change(screen.getByLabelText('Custom validation rules'), {
    target: { value: 'required|string|min:3|max:30|alpha_dash' },
  });

  expect(onUpdateSettings).toHaveBeenCalledWith('field_text', {
    validation: { rules: 'required|string|min:3|max:30|alpha_dash' },
  });
});

test('field settings panel shows numeric and upload validation controls by type', () => {
  const { rerender } = render(
    <FieldSettingsPanel
      field={{ id: 'field_number', type: 'number', label: 'Age', name: 'age', settings: {} }}
      fields={[]}
      onUpdate={() => {}}
      onUpdateSettings={() => {}}
      onDelete={() => {}}
      onDuplicate={() => {}}
      onMove={() => {}}
    />
  );

  expect(screen.getByLabelText('Minimum value')).not.toBeNull();

  rerender(
    <FieldSettingsPanel
      field={{ id: 'field_file', type: 'file_upload', label: 'Resume', name: 'resume', settings: {} }}
      fields={[]}
      onUpdate={() => {}}
      onUpdateSettings={() => {}}
      onDelete={() => {}}
      onDuplicate={() => {}}
      onMove={() => {}}
    />
  );

  expect(screen.getByLabelText('Max file size (MB)')).not.toBeNull();
  expect(screen.getByLabelText('Allowed extensions')).not.toBeNull();
});
