import { render, screen, fireEvent, waitFor, within } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import App from '../app';

jest.mock('@wordpress/api-fetch', () => jest.fn());

beforeEach(() => {
  window.bs23FormBuilder = {
    adminUrl: 'https://example.test/wp-admin/admin.php',
    page: 'builder',
  };
  apiFetch.mockImplementation((request) => {
    const { path, method } = request;
    if (path === '/bs23-form-builder/v1/forms') {
      return Promise.resolve([
        { id: 7, title: 'Contact Form', field_count: 2, entries_count: 248, entries_this_month: 82, entries_today: 9, status: 'publish', shortcode: '[bs23_form id="7"]', created_at: '2026-05-01T00:00:00+00:00' },
        { id: 9, title: 'Quote Form', field_count: 0, entries_count: 0, entries_this_month: 0, entries_today: 0, status: 'draft', shortcode: '[bs23_form id="9"]', created_at: '2026-05-02T00:00:00+00:00' },
      ]);
    }
    if (path === '/bs23-form-builder/v1/forms/7') {
      return Promise.resolve({
        id: 7,
        title: 'Contact Form',
        schema: {
          version: 1,
          fields: [{ id: 'field_1', type: 'email', label: 'Email', name: 'email' }],
        },
      });
    }
    if (path === '/bs23-form-builder/v1/forms/7/settings') {
      return Promise.resolve({
        notification: { enabled: true, to: '{admin_email}', subject: 'Subject', message: '{all_fields}', reply_to: '' },
        confirmation: { message: 'Thanks', redirect_url: '' },
        security: { enabled: true, honeypot: true, minimum_time: 3, rate_limit_count: 5, rate_limit_window: 300 },
        style: { max_width: '760px' },
      });
    }
    if (path === '/bs23-form-builder/v1/forms/7' && method === 'DELETE') {
      return Promise.resolve({ deleted: true, id: 7 });
    }
    return Promise.resolve({});
  });
});

test('renders palette groups and adds email field to canvas', async () => {
  render(<App />);

  await waitFor(() => expect(screen.getByText('General Fields')).not.toBeNull());

  expect(screen.getByText('General Fields')).not.toBeNull();

  const palette = screen.getByLabelText('Field palette');
  fireEvent.dragStart(within(palette).getByText('Email'), {
    dataTransfer: { setData: jest.fn() },
  });
  fireEvent.drop(screen.getByLabelText('Form canvas'), {
    dataTransfer: { getData: () => 'email' },
  });

  expect(within(screen.getByLabelText('Form canvas')).getByText('Email')).not.toBeNull();
});

test('double-clicking a palette field adds it to the canvas', async () => {
  render(<App />);

  await waitFor(() => expect(screen.getByText('General Fields')).not.toBeNull());

  fireEvent.doubleClick(within(screen.getByLabelText('Field palette')).getByText('Email'));

  expect(within(screen.getByLabelText('Form canvas')).getByText('Email')).not.toBeNull();
});

test('builder screen does not render the all forms library', async () => {
  render(<App />);

  await waitFor(() => expect(screen.getByText('General Fields')).not.toBeNull());

  expect(screen.getByDisplayValue('Untitled Form')).not.toBeNull();
  expect(screen.queryByLabelText('Forms library')).toBeNull();
});

test('builder screen opens a specific form from the menu action URL', async () => {
  window.bs23FormBuilder.formId = 7;

  render(<App />);

  await waitFor(() => expect(screen.getByDisplayValue('Contact Form')).not.toBeNull());

  expect(within(screen.getByLabelText('Form canvas')).getByText('Email')).not.toBeNull();
});

test('all forms screen shows dashboard table with form actions', async () => {
  window.bs23FormBuilder.page = 'all_forms';

  render(<App />);

  await waitFor(() => expect(screen.getByText('All Forms')).not.toBeNull());

  expect(screen.getByText('Total Submissions')).not.toBeNull();
  expect(screen.getByText('This Month')).not.toBeNull();
  expect(screen.getAllByText('Today').length).toBeGreaterThan(0);
  expect(screen.getAllByText('248').length).toBeGreaterThan(0);
  expect(screen.getByText('Contact Form')).not.toBeNull();
  expect(screen.getByText('[bs23_form id="7"]')).not.toBeNull();

  fireEvent.click(screen.getAllByRole('button', { name: 'Actions' })[0]);

  expect(screen.getByText('Edit')).not.toBeNull();
  expect(screen.getByText('Settings')).not.toBeNull();
  expect(screen.getAllByText('Entries').length).toBeGreaterThan(0);
  expect(screen.getByText('Preview')).not.toBeNull();
  expect(screen.getByText('Delete')).not.toBeNull();
});

test('organizes builder tools into inspector tabs', async () => {
  render(<App />);

  await waitFor(() => expect(screen.getByRole('tab', { name: 'Fields' })).not.toBeNull());

  expect(screen.getByText('General Fields')).not.toBeNull();

  fireEvent.click(screen.getByRole('tab', { name: 'Email' }));
  expect(screen.getByText('Email notification')).not.toBeNull();

  fireEvent.click(screen.getByRole('tab', { name: 'Style' }));
  expect(screen.getByText('Form width')).not.toBeNull();

  fireEvent.click(screen.getByRole('tab', { name: 'Security' }));
  expect(screen.getByText('Enable anti-spam')).not.toBeNull();
});
