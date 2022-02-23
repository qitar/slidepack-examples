#! /usr/bin/env ruby

require 'uri'
require 'net/http'
require 'json'

SLIDEPACK_API_ENDPOINT = 'https://slidepack.io'.freeze

def create_session(api_token)
  _post_request_with_json("#{SLIDEPACK_API_ENDPOINT}/sessions", api_token)
end

def execute_render(session_uuid, api_token)
  _post_request_with_json("#{SLIDEPACK_API_ENDPOINT}/sessions/#{session_uuid}/render", api_token)
end

def upload_zip_file(local_zip_file_path, session_info)
  uri = URI(session_info['upload']['action'])

  http = Net::HTTP.new(uri.host, uri.port)
  http.use_ssl = true

  req = Net::HTTP::Post.new(uri.request_uri)
  params = session_info['upload']['params'].keys.map do |k| 
    [k, session_info['upload']['params'][k]]
  end
  params << ['file', File.open(local_zip_file_path)]
  req.set_form(params, 'multipart/form-data')

  res = http.request(req)
  res.code == '204'
end

def download_pptx(download_url, local_pptx_file_path)
  uri = URI(download_url)
  http = Net::HTTP.new(uri.host, uri.port)
  http.use_ssl = true
  http.verify_mode = OpenSSL::SSL::VERIFY_NONE

  resp = http.get(uri)
  File.open(local_pptx_file_path, 'wb') do |file|
    file.write(resp.body)
  end
end

def _post_request_with_json(uri_str, api_token)
  uri = URI(uri_str)

  http = Net::HTTP.new(uri.host, uri.port)
  http.use_ssl = true

  req = Net::HTTP::Post.new(uri.request_uri)
  req['Content-Type'] = 'application/json'
  req['Authorization'] = "Bearer #{api_token}"

  res = http.request(req)

  return nil unless res.is_a?(Net::HTTPSuccess)

  JSON.parse(res.body)
end


if __FILE__ == $PROGRAM_NAME
  session_info = create_session(ENV['SLIDEPACK_API_TOKEN'])
  if session_info.nil?
    STDERR.puts 'ERROR: failed to create session.'
    exit(1)
  end

  unless upload_zip_file(File.expand_path('./template.zip'), session_info)
    STDERR.puts 'ERROR: failed to upload zip file.'
    exit(2)
  end

  render_info = execute_render(session_info['session']['uuid'], ENV['SLIDEPACK_API_TOKEN'])
  if render_info.nil?
    STDERR.puts 'ERROR: failed to execute render.'
    exit(3)
  end

  download_pptx(render_info['download_url'], './output.pptx')
end
