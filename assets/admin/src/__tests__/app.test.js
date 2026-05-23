import { render, screen, fireEvent, waitFor, within } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import App from '../app';

jest.mock('@wordpress/api-fetch', () => jest.fn());

beforeEach(() => {
  apiFetch.mockImplementation(({ path }) => {
    if (path === '/bs23-form-builder/v1/forms') {
      return Promise.resolve([
        { id: 7, title: 'Contact Form', field_count: 2 },
        { id: 9, title: 'Quote Form', field_count: 0 },
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

test('loads forms list and selects a saved form', async () => {
  render(<App />);

  await waitFor(() => expect(screen.getByText('Contact Form')).not.toBeNull());

  fireEvent.click(screen.getByText('Contact Form'));

  await waitFor(() => expect(screen.getByDisplayValue('Contact Form')).not.toBeNull());
  expect(within(screen.getByLabelText('Form canvas')).getByText('Email')).not.toBeNull();
});

test('new form resets builder to a draft', async () => {
  render(<App />);

  await waitFor(() => expect(screen.getByText('Contact Form')).not.toBeNull());
  fireEvent.click(screen.getByText('Contact Form'));
  await waitFor(() => expect(screen.getByDisplayValue('Contact Form')).not.toBeNull());

  fireEvent.click(screen.getByText('New Form'));

  expect(screen.getByDisplayValue('Untitled Form')).not.toBeNull();
  expect(screen.getByText('Drop fields here')).not.toBeNull();
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
