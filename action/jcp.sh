#!/bin/bash -e

if [[ -z "${anodosysAppPath}" ]]; then
  >&2 echo "No anodosys app path defined"
  exit 1
fi

if [[ -z "${systemName}" ]]; then
  >&2 echo "No system name specified!"
  exit 1
fi

setServerConfiguration "${systemName}" "global"

if [[ -n "${appServerName}" ]]; then
  serverName="${appServerName}"
else
  serverName="cli"
fi

setServerConfiguration "${systemName}" "${serverName}"

if [[ -z "${appPath}" ]]; then
  >&2 echo "No app path specified!"
  exit 1
fi

if [[ -n "${appUser}" ]]; then
  userName="${appUser}"
else
  userName="me"
fi

shift

"${anodosysAppPath}/server/container/command.sh" \
  -s "${serverName}" \
  -u "${userName}" \
  -w "${appPath}" \
  -c "php jcp ${*}" \
  -i \
  -q
